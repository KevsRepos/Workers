<?php

declare(strict_types=1);

namespace App\Shared\Exception;

final class ConflictException extends ApiException
{
    public function getStatusCode(): int
    {
        return 409;
    }

    public function getType(): string
    {
        return 'conflict';
    }

    public function getTitle(): string
    {
        return 'Conflict with current state';
    }
}
