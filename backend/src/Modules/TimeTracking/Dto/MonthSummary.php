<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

final readonly class MonthSummary
{
    public function __construct(
        public int $month,
        public int $entries,
        public float $totalHours,
    ) {
    }
}
