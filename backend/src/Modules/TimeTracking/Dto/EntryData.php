<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

use App\Modules\TimeTracking\TimeSheetEntryType;
use Symfony\Component\Validator\Constraints as Assert;

final class EntryData
{
    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 31)]
    public ?int $day = null;

    #[Assert\NotNull]
    public ?TimeSheetEntryType $type = TimeSheetEntryType::WORK;

    #[Assert\When(expression: 'this.type?.value === "work"', constraints: [new Assert\NotBlank(message: 'Start time is required.')])]
    #[Assert\Regex(pattern: '/^([01]\d|2[0-3]):[0-5]\d$/', message: 'Use the format HH:MM.')]
    public ?string $start = null;

    #[Assert\When(expression: 'this.type?.value === "work"', constraints: [new Assert\NotBlank(message: 'End time is required.')])]
    #[Assert\Regex(pattern: '/^([01]\d|2[0-3]):[0-5]\d$/', message: 'Use the format HH:MM.')]
    public ?string $end = null;

    #[Assert\Range(min: 0, max: 720)]
    public ?int $breakMinutes = null;

    #[Assert\Length(max: 500)]
    public ?string $note = null;
}
