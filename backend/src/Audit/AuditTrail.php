<?php

declare(strict_types=1);

namespace App\Audit;

use App\Modules\Account\Account;
use App\Shared\Http\RequestContext;
use App\Shared\Security\CurrentUser;

/**
 * Entry point for recording audit events with automatically resolved actor and request context.
 */
final readonly class AuditTrail
{
    public function __construct(
        private AuditWriter $writer,
        private CurrentUser $currentUser,
        private RequestContext $requestContext,
    ) {
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function record(string $action, ?string $subjectType = null, ?string $subjectId = null, ?array $data = null, ?Account $actor = null): void
    {
        $this->writer->append([$this->createEvent($action, $subjectType, $subjectId, $data, $actor)]);
    }

    /**
     * @param array<string, mixed>|null $changes
     */
    public function createEvent(string $action, ?string $subjectType, ?string $subjectId, ?array $changes, ?Account $actor = null): AuditEvent
    {
        $actor ??= $this->currentUser->accountOrNull();

        return new AuditEvent(
            $action,
            $actor?->id,
            $actor->emailAddress ?? ($this->requestContext->getRequestId() === null ? 'system' : null),
            $subjectType,
            $subjectId,
            $changes,
            $this->context(),
        );
    }

    /**
     * @param list<AuditEvent> $events
     */
    public function append(array $events): void
    {
        $this->writer->append($events);
    }

    /**
     * @return array<string, string>
     */
    private function context(): array
    {
        return array_filter([
            'channel' => $this->requestContext->getRequestId() !== null ? 'http' : 'system',
            'requestId' => $this->requestContext->getRequestId(),
            'ip' => $this->requestContext->getClientIp(),
            'userAgent' => $this->requestContext->getUserAgent(),
        ], static fn (?string $value): bool => $value !== null);
    }
}
