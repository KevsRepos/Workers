<?php

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDeliveryNoteRequestDto
{
    #[Assert\NotBlank]
    public string $customerId;

    #[Assert\NotBlank]
    public string $deliveryDate;

    #[Assert\Type('bool')]
    public bool $delivery = false;

    public ?string $shortDescription = null;

    public ?string $assignment = null;

    /** @var DeliveryNoteProductDto[] */
    #[Assert\NotBlank]
    #[Assert\Valid]
    public array $deliveryNoteProducts = [];
}
