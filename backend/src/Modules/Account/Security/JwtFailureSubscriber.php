<?php

declare(strict_types=1);

namespace App\Modules\Account\Security;

use App\Shared\Http\ProblemResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Renders missing/invalid/expired access tokens as RFC 9457 problem details.
 */
final readonly class JwtFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_NOT_FOUND => 'onFailure',
            Events::JWT_INVALID => 'onFailure',
            Events::JWT_EXPIRED => 'onFailure',
            Events::AUTHENTICATION_FAILURE => 'onFailure',
        ];
    }

    public function onFailure(AuthenticationFailureEvent $event): void
    {
        $request = $event->getRequest() ?? $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        $expired = $event instanceof JWTExpiredEvent;
        $response = ProblemResponse::create(
            $request,
            401,
            $expired ? 'token-expired' : 'unauthenticated',
            'Authentication required',
            $expired ? 'The access token has expired.' : 'Valid authentication is required.',
        );
        $response->headers->set('WWW-Authenticate', 'Bearer');
        $event->setResponse($response);
    }
}
