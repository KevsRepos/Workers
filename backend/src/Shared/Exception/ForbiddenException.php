<?php

declare(strict_types=1);

namespace App\Shared\Exception;

final class ForbiddenException extends ApiException
{
    public function getStatusCode(): int
    {
        return 403;
    }

    public function getType(): string
    {
        return 'forbidden';
    }

    public function getTitle(): string
    {
        return 'Forbidden';
    }
}
