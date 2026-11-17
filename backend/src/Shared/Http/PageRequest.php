<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * Validated pagination parameters (?page=1&limit=25).
 */
final readonly class PageRequest
{
    public function __construct(public int $page, public int $limit)
    {
    }

    public static function fromRequest(Request $request, int $defaultLimit = 25, int $maxLimit = 100): self
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', $defaultLimit);
        $limit = max(1, min($maxLimit, $limit));

        return new self($page, $limit);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
