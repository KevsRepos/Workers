<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMonthlyTimeSheetEntryRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 31)]
    public int $day;

    #[Assert\NotBlank]
    public string $start;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    public int $breakDuration;

    #[Assert\NotBlank]
    public string $end;
}
