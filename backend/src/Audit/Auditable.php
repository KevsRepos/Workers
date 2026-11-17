<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * Configures how an entity appears in the audit trail.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Auditable
{
    /**
     * @param list<string> $ignore fields never recorded (noise, e.g. timestamps)
     * @param list<string> $redact fields recorded only as "changed" (secrets)
     */
    public function __construct(
        public bool $enabled = true,
        public array $ignore = [],
        public array $redact = [],
    ) {
    }
}
