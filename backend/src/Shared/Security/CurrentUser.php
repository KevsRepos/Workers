<?php

declare(strict_types=1);

namespace App\Shared\Security;

use App\Modules\Account\Account;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Access to the authenticated account.
 */
final readonly class CurrentUser
{
    public function __construct(private Security $security)
    {
    }

    public function account(): Account
    {
        $user = $this->security->getUser();
        if (!$user instanceof Account) {
            throw new AccessDeniedException('Authentication required.');
        }

        return $user;
    }

    public function accountOrNull(): ?Account
    {
        $user = $this->security->getUser();

        return $user instanceof Account ? $user : null;
    }

    public function idOrNull(): ?string
    {
        return $this->accountOrNull()?->id;
    }

    public function isGranted(string $attribute, mixed $subject = null): bool
    {
        return $this->security->isGranted($attribute, $subject);
    }
}
