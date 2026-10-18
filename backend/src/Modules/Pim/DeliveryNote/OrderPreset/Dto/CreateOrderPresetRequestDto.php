<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderPresetRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    /** @var string[] */
    #[Assert\All([new Assert\NotBlank(), new Assert\Type('string')])]
    public array $productIds = [];
}