<?php

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\DeliveryNoteStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDeliveryNoteRequestDto
{
    public ?string $customerId = null;

    public string $deliveryDate = '';

    public ?bool $delivery = false;

    public ?string $shortDescription = null;

    public ?string $assignment = null;

    /** @var DeliveryNoteProductDto[] */
    #[Assert\Valid]
    public array $deliveryNoteProducts = [];

    /** @var string[] */
    public array $removedProductIds = [];

    public ?DeliveryNoteStatus $status = null;
}
