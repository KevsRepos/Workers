<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

readonly final class MonthlyTimeSheetEntryResponseDto
{
    public function __construct(
        public string $id,
        public int $day,
        public string $start,
        public int $breakDuration,
        public string $end,
        public float $totalHours,
    ) {}
}
