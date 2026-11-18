<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Security;

use App\Modules\Account\Account;
use App\Modules\Account\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Time sheets are personal data: only the owner and administrators may read or change them.
 *
 * @extends Voter<string, Account>
 */
final class TimeSheetVoter extends Voter
{
    public const string VIEW = 'TIME_SHEET_VIEW';
    public const string EDIT = 'TIME_SHEET_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT], true) && $subject instanceof Account;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Account) {
            return false;
        }

        return $user === $subject || $user->id === $subject->id || \in_array(Role::ADMIN, $user->getRoles(), true);
    }
}
