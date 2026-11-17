<?php

declare(strict_types=1);

namespace App\Modules\Pim\Product\Dto;

use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression('this.sellable or this.rentable', message: 'A product must be sellable, rentable or both.')]
final class ProductData
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name = '';

    /** Cents. */
    #[Assert\Range(min: 0, max: 100000000)]
    public ?int $salesPrice = null;

    #[Assert\Uuid]
    public ?string $depositId = null;

    public bool $sellable = false;

    public bool $rentable = false;

    #[Assert\Range(min: 1, max: 1000)]
    public ?int $quantityInCrate = null;

    public bool $archived = false;
}
