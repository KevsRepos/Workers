<?php

namespace App\Modules\Pim\ProductUnion\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductUnionRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Count(min: 2, minMessage: 'Eine Produktvereinigung muss mindestens 2 Produkte enthalten.')]
    public array $productIds;
}
