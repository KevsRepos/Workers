<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

readonly final class MonthlyTimeSheetResponseDto
{
    public function __construct(
        public string $id,
        public int $month,
        public int $year,
        public array $entries,
        public float $totalHours,
        public ?string $accountFirstName = null,
        public ?string $accountSurname = null,
        public ?string $accountEmailAddress = null,
    ) {}
}
