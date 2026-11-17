<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\DeliveryNote;

final readonly class DeliveryNoteSummary
{
    public function __construct(
        public string $id,
        public string $number,
        public int $status,
        public string $statusLabel,
        public string $deliveryDate,
        public ?string $pickupDate,
        public bool $delivery,
        public string $customerId,
        public string $customerName,
        public ?string $shippingAddress,
        public ?string $shortDescription,
        public ?string $assignment,
        public bool $callBefore,
    ) {
    }

    public static function fromEntity(DeliveryNote $note): self
    {
        return new self(
            (string) $note->id,
            $note->number,
            $note->status->value,
            $note->status->label(),
            $note->deliveryDate->format('Y-m-d'),
            $note->pickupDate?->format('Y-m-d'),
            $note->delivery,
            (string) $note->customer->id,
            $note->customer->displayName,
            $note->shippingAddress?->singleLine,
            $note->shortDescription,
            $note->assignment,
            $note->callBefore,
        );
    }
}
