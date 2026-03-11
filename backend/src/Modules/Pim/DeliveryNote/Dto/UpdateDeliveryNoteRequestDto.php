<?php

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\DeliveryNoteStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDeliveryNoteRequestDto
{
    public ?string $customerId;

    public string $deliveryDate;

    public ?bool $delivery = false;

    /** @var DeliveryNoteProductDto[] */
    #[Assert\Valid]
    public array $deliveryNoteProducts = [];

    public ?DeliveryNoteStatus $status = null;
}
