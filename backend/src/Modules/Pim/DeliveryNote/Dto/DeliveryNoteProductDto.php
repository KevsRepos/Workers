<?php

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DeliveryNoteProductDto
{
    public ?string $id = null;

    #[Assert\NotBlank]
    public string $productId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quantity;
}
