<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

final readonly class DayResponse
{
    public function __construct(
        public int $day,
        public string $date,
        public int $weekday,
        public bool $weekend,
        public bool $future,
        public ?EntryResponse $entry,
    ) {
    }
}
