<?php

namespace App\Modules\Pim\Deposit\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDepositRequestDto
{
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    public int $singleAmount;

    #[Assert\GreaterThanOrEqual(0)]
    public ?int $crateAmount = null;
}
