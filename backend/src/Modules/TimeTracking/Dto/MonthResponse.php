<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

final readonly class MonthResponse
{
    /**
     * @param list<DayResponse>  $days
     * @param array<string, int> $countsByType
     */
    public function __construct(
        public ?string $id,
        public int $year,
        public int $month,
        public string $accountId,
        public string $accountName,
        public array $days,
        public float $workedHours,
        public float $absenceHours,
        public float $totalHours,
        public array $countsByType,
        public int $warningCount,
    ) {
    }
}
