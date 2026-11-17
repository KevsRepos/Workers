<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryNoteLineData
{
    /** Existing line id when updating; null for new lines. */
    #[Assert\Uuid]
    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $productId = '';

    /** null = quantity not yet known (e.g. rental where the amount is decided on site). */
    #[Assert\Positive]
    #[Assert\LessThan(1000000)]
    public ?float $quantity = null;
}
