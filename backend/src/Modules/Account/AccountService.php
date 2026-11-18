<?php

declare(strict_types=1);

namespace App\Modules\Account;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Modules\Account\Dto\CreateAccountRequest;
use App\Modules\Account\Dto\UpdateAccountRequest;
use App\Modules\Account\RefreshToken\RefreshTokenService;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\ConflictException;
use App\Shared\Exception\NotFoundException;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class AccountService
{
    public function __construct(
        private Repository $accounts,
        private UserPasswordHasherInterface $hasher,
        private RefreshTokenService $refreshTokens,
        private AuditTrail $auditTrail,
        private ClockInterface $clock,
    ) {
    }

    public function get(string $id): Account
    {
        return $this->accounts->find($id) ?? throw NotFoundException::resource('Account', $id);
    }

    /**
     * @return list<Account>
     */
    public function list(): array
    {
        return $this->accounts->findAllOrdered();
    }

    public function create(CreateAccountRequest $request): Account
    {
        if ($this->accounts->findByEmail($request->emailAddress) !== null) {
            throw new ConflictException('An account with this email address already exists.');
        }

        $account = new Account($request->emailAddress, $request->firstName, $request->surname);
        $account->roles = self::normalizeRoles($request->roles);
        $this->setPassword($account, $request->password);
        $this->accounts->add($account);

        return $account;
    }

    public function update(Account $account, UpdateAccountRequest $request, Account $actor): Account
    {
        $email = Account::normalizeEmail($request->emailAddress);
        $existing = $this->accounts->findByEmail($email);
        if ($existing !== null && $existing !== $account) {
            throw new ConflictException('An account with this email address already exists.');
        }

        $roles = self::normalizeRoles($request->roles);
        $losesAdmin = $account->isAdmin() && (!\in_array(Role::ADMIN, $roles, true) || !$request->active);

        if ($account === $actor && $losesAdmin) {
            throw BusinessRuleException::forField('roles', 'You cannot remove your own administrator rights or deactivate yourself.', 'self-lockout');
        }
        if ($losesAdmin && $account->active && $this->accounts->countActiveAdmins() <= 1) {
            throw BusinessRuleException::forField('roles', 'At least one active administrator must remain.', 'last-admin');
        }

        $account->emailAddress = $email;
        $account->firstName = trim($request->firstName);
        $account->surname = trim($request->surname);
        $account->roles = $roles;

        if ($account->active && !$request->active) {
            $this->refreshTokens->revokeAll($account);
        }
        $account->active = $request->active;

        return $account;
    }

    public function changeOwnPassword(Account $account, string $currentPassword, string $newPassword, ?string $keepSessionOfToken): void
    {
        if (!$this->hasher->isPasswordValid($account, $currentPassword)) {
            throw BusinessRuleException::forField('currentPassword', 'The current password is incorrect.', 'invalid-password');
        }
        if ($this->hasher->isPasswordValid($account, $newPassword)) {
            throw BusinessRuleException::forField('newPassword', 'The new password must differ from the current password.', 'password-unchanged');
        }

        $this->setPassword($account, $newPassword);
        $this->refreshTokens->revokeAll($account, $keepSessionOfToken);
        $this->auditTrail->record(AuditAction::AUTH_PASSWORD_CHANGED, subjectType: 'Account', subjectId: $account->id);
    }

    public function resetPassword(Account $account, string $newPassword): void
    {
        $this->setPassword($account, $newPassword);
        $this->refreshTokens->revokeAll($account);
        $this->auditTrail->record(AuditAction::AUTH_PASSWORD_RESET, subjectType: 'Account', subjectId: $account->id);
    }

    private function setPassword(Account $account, string $plainPassword): void
    {
        $account->password = $this->hasher->hashPassword($account, $plainPassword);
        $account->passwordChangedAt = $this->clock->now();
    }

    /**
     * @param list<string> $roles
     *
     * @return list<string>
     */
    private static function normalizeRoles(array $roles): array
    {
        $roles = array_values(array_unique(array_intersect($roles, Role::ASSIGNABLE)));
        sort($roles);

        return $roles === [] ? [Role::USER] : $roles;
    }
}
