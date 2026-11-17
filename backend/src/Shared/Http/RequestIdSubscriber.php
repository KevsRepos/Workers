<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

/**
 * Assigns a correlation id to every request (accepting a well-formed upstream X-Request-Id)
 * and echoes it in the response.
 */
final readonly class RequestIdSubscriber implements EventSubscriberInterface
{
    public const string HEADER = 'X-Request-Id';
    public const string ATTRIBUTE = '_request_id';

    public function __construct(private RequestContext $context)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 4096],
            KernelEvents::RESPONSE => ['onResponse', -4096],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $incoming = (string) $request->headers->get(self::HEADER, '');
        $requestId = preg_match('/^[A-Za-z0-9._-]{8,64}$/', $incoming) === 1 ? $incoming : Uuid::v4()->toRfc4122();

        $request->attributes->set(self::ATTRIBUTE, $requestId);
        $this->context->initialize($requestId, $request->getClientIp(), $request->headers->get('User-Agent'));
    }

    public function onResponse(ResponseEvent $event): void
    {
        $requestId = $event->getRequest()->attributes->get(self::ATTRIBUTE);
        if (\is_string($requestId)) {
            $event->getResponse()->headers->set(self::HEADER, $requestId);
        }
    }
}
