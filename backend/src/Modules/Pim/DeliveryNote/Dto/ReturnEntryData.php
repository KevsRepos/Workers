<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ReturnEntryData
{
    /** @var list<string> */
    #[Assert\Count(min: 1)]
    #[Assert\Unique]
    #[Assert\All([new Assert\Uuid()])]
    public array $lineIds = [];

    #[Assert\PositiveOrZero]
    public ?int $returnedTotal = null;

    #[Assert\PositiveOrZero]
    public ?int $returnedTotalBottles = null;

    #[Assert\PositiveOrZero]
    #[Assert\Expression('value === null or this.returnedTotal === null or value <= this.returnedTotal', message: 'Full crates cannot exceed the total returned.')]
    public ?int $returnedFull = null;

    #[Assert\PositiveOrZero]
    #[Assert\Expression('value === null or this.returnedTotalBottles === null or value <= this.returnedTotalBottles', message: 'Full bottles cannot exceed the total returned bottles.')]
    public ?int $returnedFullBottles = null;
}
