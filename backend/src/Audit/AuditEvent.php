<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * An audit event before it is appended to the chain.
 */
final readonly class AuditEvent
{
    /**
     * @param array<string, mixed>|null $changes
     * @param array<string, mixed>      $context
     */
    public function __construct(
        public string $action,
        public ?string $actorId,
        public ?string $actorLabel,
        public ?string $subjectType,
        public ?string $subjectId,
        public ?array $changes,
        public array $context,
    ) {
    }
}
