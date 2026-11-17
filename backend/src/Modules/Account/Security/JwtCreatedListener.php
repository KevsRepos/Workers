<?php

declare(strict_types=1);

namespace App\Modules\Account\Security;

use App\Modules\Account\Account;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adds display claims to the access token. Authorization never relies on these claims: the
 * backend reloads the account (roles, active flag) on every request.
 */
#[AsEventListener(event: Events::JWT_CREATED)]
final class JwtCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Account) {
            return;
        }

        $payload = $event->getData();
        $payload['name'] = $user->displayName;
        $payload['email'] = $user->emailAddress;
        $payload['roles'] = $user->getRoles();
        $event->setData($payload);
    }
}
