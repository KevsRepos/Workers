<?php

declare(strict_types=1);

namespace App\Shared\Exception;

/**
 * Base class for expected, client-facing errors. Rendered as RFC 9457 problem details.
 *
 * The message is safe to expose to clients; never put internal details into it.
 */
abstract class ApiException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $extensions additional problem detail members
     */
    public function __construct(
        string $detail,
        private readonly array $extensions = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($detail, 0, $previous);
    }

    abstract public function getStatusCode(): int;

    /**
     * Machine readable, stable error identifier (e.g. "not-found").
     */
    abstract public function getType(): string;

    abstract public function getTitle(): string;

    /**
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
