<?php

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DeliveryNoteProductDto
{
    #[Assert\NotBlank]
    public string $productId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quantity;
}
