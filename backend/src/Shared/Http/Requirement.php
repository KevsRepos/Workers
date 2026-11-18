<?php

declare(strict_types=1);

namespace App\Shared\Http;

/**
 * Route requirement patterns.
 */
final class Requirement
{
    public const string UUID = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';
}
