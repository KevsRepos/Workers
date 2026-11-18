<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

use App\Modules\TimeTracking\MonthlyTimeSheetEntry;

final readonly class EntryResponse
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public string $id,
        public int $day,
        public string $date,
        public string $type,
        public string $typeLabel,
        public ?string $start,
        public ?string $end,
        public ?int $breakMinutes,
        public bool $endsNextDay,
        public ?string $note,
        public float $hours,
        public array $warnings,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param list<string> $warnings
     */
    public static function fromEntity(MonthlyTimeSheetEntry $entry, float $hours, array $warnings): self
    {
        return new self(
            (string) $entry->id,
            $entry->day,
            $entry->date()->format('Y-m-d'),
            $entry->type->value,
            $entry->type->label(),
            $entry->start?->format('H:i'),
            $entry->end?->format('H:i'),
            $entry->breakMinutes,
            $entry->endsNextDay(),
            $entry->note,
            $hours,
            $warnings,
            $entry->updatedAt ?? $entry->createdAt,
        );
    }
}
