<?php

declare(strict_types=1);

namespace App\Shared\Exception;

final class BadRequestException extends ApiException
{
    public function getStatusCode(): int
    {
        return 400;
    }

    public function getType(): string
    {
        return 'bad-request';
    }

    public function getTitle(): string
    {
        return 'Bad request';
    }
}
