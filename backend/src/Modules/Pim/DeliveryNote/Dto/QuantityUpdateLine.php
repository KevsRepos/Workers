<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class QuantityUpdateLine
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $id = '';

    #[Assert\Positive]
    #[Assert\LessThan(1000000)]
    public ?float $quantity = null;
}
