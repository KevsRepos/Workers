<?php

// Intentionally WITHOUT declare(strict_types=1): controllers must be invoked with PHP's
// coercive scalar handling (route attribute "2026" → int $year), exactly like HttpKernel does.

namespace App\Shared\Http;

final class ControllerInvoker
{
    /**
     * @param array<int|string, mixed> $arguments
     */
    public static function invoke(callable $controller, array $arguments): mixed
    {
        return $controller(...$arguments);
    }
}
