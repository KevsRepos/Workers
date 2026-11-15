<?php
namespace App\Modules\Account\RefreshToken;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Modules\Account\RefreshToken\Service;

class AuthenticationSuccessListener implements EventSubscriberInterface
{
    public function __construct(
        private Service $service,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        $token = $this->service->saveRefreshToken($user->getUserIdentifier());
        
        $this->service->setRefreshTokenInHttpOnlyCookie($event->getResponse(), $token);
    }
}