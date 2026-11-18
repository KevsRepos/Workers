<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderPresetData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name = '';

    /** @var list<string> */
    #[Assert\Count(min: 1, minMessage: 'Select at least one product.')]
    #[Assert\Unique]
    #[Assert\All([new Assert\Uuid()])]
    public array $productIds = [];
}
