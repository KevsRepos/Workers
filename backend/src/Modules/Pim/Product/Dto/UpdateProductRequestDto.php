<?php

namespace App\Modules\Pim\Product\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $name;

    #[Assert\Type('numeric')]
    #[Assert\GreaterThanOrEqual(0)]
    public ?int $salesPrice;

    public ?string $depositId;

    #[Assert\Type('bool')]
    public bool $sellable = false;

    #[Assert\Type('bool')]
    public bool $rentable = false;

    public ?int $quantityInCrate;
}