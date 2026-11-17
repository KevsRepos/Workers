<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class QuantityUpdateRequest
{
    /** @var list<QuantityUpdateLine> */
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $lines = [];
}
