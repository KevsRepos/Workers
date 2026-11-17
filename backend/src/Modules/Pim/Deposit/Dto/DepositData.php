<?php

declare(strict_types=1);

namespace App\Modules\Pim\Deposit\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DepositData
{
    #[Assert\Length(max: 100)]
    public ?string $label = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100000)]
    public int $singleAmount = 0;

    #[Assert\Range(min: 0, max: 100000)]
    public ?int $crateAmount = null;
}
