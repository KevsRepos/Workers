<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ProductUnionData
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name = '';

    /** @var list<string> */
    #[Assert\Count(min: 2, minMessage: 'A product union must contain at least {{ limit }} products.')]
    #[Assert\Unique]
    #[Assert\All([new Assert\Uuid()])]
    public array $productIds = [];
}
