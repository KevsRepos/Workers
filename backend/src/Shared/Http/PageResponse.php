<?php

declare(strict_types=1);

namespace App\Shared\Http;

/**
 * @template T
 */
final readonly class PageResponse
{
    public int $pages;

    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
        public int $total,
    ) {
        $this->pages = max(1, (int) ceil($total / max(1, $limit)));
    }
}
