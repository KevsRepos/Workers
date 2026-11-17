<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Runs every state-changing API request (POST/PUT/PATCH/DELETE) in one database transaction.
 *
 * All writes of a use case, including the audit trail rows written during flush, are
 * committed atomically or not at all. Exceptions roll the transaction back and are then
 * rendered by the problem details subscriber.
 */
final readonly class TransactionalControllerSubscriber implements EventSubscriberInterface
{
    private const array WRITE_METHODS = [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH, Request::METHOD_DELETE];

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => ['wrapController', -1024]];
    }

    public function wrapController(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest() || !\in_array($event->getRequest()->getMethod(), self::WRITE_METHODS, true)) {
            return;
        }

        $controller = $event->getController();
        $entityManager = $this->entityManager;

        $event->setController(
            static fn (mixed ...$arguments): mixed => $entityManager->wrapInTransaction(
                static fn (): mixed => $controller(...$arguments),
            ),
            $event->getAttributes(),
        );
    }
}
