<?php

declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Modules\Account\Account;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Uid\Uuid;

/**
 * Issues, rotates and revokes refresh tokens.
 */
final readonly class RefreshTokenService
{
    public function __construct(
        private Repository $repository,
        private ClockInterface $clock,
        private AuditTrail $auditTrail,
        #[Autowire('%app.auth.refresh_token_ttl%')]
        private int $tokenTtl,
        #[Autowire('%app.auth.session_max_lifetime%')]
        private int $sessionMaxLifetime,
    ) {
    }

    /**
     * Starts a new login session.
     *
     * @return array{string, RefreshToken} plain token and its entity
     */
    public function issue(Account $account): array
    {
        $now = $this->clock->now();

        return $this->create($account, Uuid::v7()->toRfc4122(), $now->modify(\sprintf('+%d seconds', $this->sessionMaxLifetime)));
    }

    /**
     * Rotates a refresh token. Reuse of a rotated token revokes the entire session family.
     *
     * @return array{string, RefreshToken}
     */
    public function rotate(string $plainToken): array
    {
        $now = $this->clock->now();
        $token = $this->repository->findByHash(self::hash($plainToken));

        if ($token === null) {
            throw new CustomUserMessageAuthenticationException('Invalid refresh token.');
        }

        if ($token->rotatedAt !== null && $token->revokedAt === null) {
            $this->repository->revokeFamily($token->familyId, $now);
            $this->auditTrail->record(AuditAction::AUTH_REFRESH_TOKEN_REUSE, subjectType: 'Account', subjectId: $token->account->id, data: ['familyId' => $token->familyId], actor: $token->account);

            throw new CustomUserMessageAuthenticationException('Invalid refresh token.');
        }

        if (!$token->isUsable($now) || !$token->account->active) {
            throw new CustomUserMessageAuthenticationException('Invalid refresh token.');
        }

        $token->rotatedAt = $now;

        return $this->create($token->account, $token->familyId, $token->sessionExpiresAt);
    }

    /**
     * Ends the session the given token belongs to. Unknown tokens are ignored (idempotent logout).
     */
    public function revokeSession(string $plainToken): ?Account
    {
        $token = $this->repository->findByHash(self::hash($plainToken));
        if ($token === null) {
            return null;
        }

        $this->repository->revokeFamily($token->familyId, $this->clock->now());

        return $token->account;
    }

    public function revokeAll(Account $account, ?string $keepSessionOfToken = null): void
    {
        $keepFamily = null;
        if ($keepSessionOfToken !== null) {
            $token = $this->repository->findByHash(self::hash($keepSessionOfToken));
            if ($token !== null && $token->account === $account) {
                $keepFamily = $token->familyId;
            }
        }

        $this->repository->revokeAllForAccount($account, $this->clock->now(), $keepFamily);
    }

    public function purgeExpired(int $graceDays = 30): int
    {
        return $this->repository->deleteExpired($this->clock->now()->modify(\sprintf('-%d days', $graceDays)));
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /**
     * @return array{string, RefreshToken}
     */
    private function create(Account $account, string $familyId, \DateTimeImmutable $sessionExpiresAt): array
    {
        $now = $this->clock->now();
        $plain = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');

        $token = new RefreshToken();
        $token->account = $account;
        $token->tokenHash = self::hash($plain);
        $token->familyId = $familyId;
        $token->sessionExpiresAt = $sessionExpiresAt;
        $expires = $now->modify(\sprintf('+%d seconds', $this->tokenTtl));
        $token->expiresAt = $expires < $sessionExpiresAt ? $expires : $sessionExpiresAt;

        $this->repository->add($token);

        return [$plain, $token];
    }
}
