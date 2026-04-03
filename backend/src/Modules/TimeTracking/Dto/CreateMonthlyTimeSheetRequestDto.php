<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMonthlyTimeSheetRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 12)]
    public int $month;

    #[Assert\NotBlank]
    #[Assert\Range(min: 2020, max: 2100)]
    public int $year;
}
