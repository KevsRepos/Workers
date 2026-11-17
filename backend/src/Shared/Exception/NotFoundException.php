<?php

declare(strict_types=1);

namespace App\Shared\Exception;

final class NotFoundException extends ApiException
{
    public static function resource(string $resource, ?string $id = null): self
    {
        return new self($id === null ? \sprintf('%s not found.', $resource) : \sprintf('%s "%s" not found.', $resource, $id), ['resource' => $resource]);
    }

    public function getStatusCode(): int
    {
        return 404;
    }

    public function getType(): string
    {
        return 'not-found';
    }

    public function getTitle(): string
    {
        return 'Resource not found';
    }
}
