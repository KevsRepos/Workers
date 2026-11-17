<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Customer\Dto\AddressResponse;
use App\Modules\Customer\Dto\CustomerSummary;
use App\Modules\Pim\DeliveryNote\DeliveryNote;

final readonly class DeliveryNoteResponse
{
    /**
     * @param list<DeliveryNoteLineResponse> $lines
     */
    public function __construct(
        public string $id,
        public string $number,
        public int $status,
        public string $statusLabel,
        public string $deliveryDate,
        public ?string $pickupDate,
        public bool $delivery,
        public CustomerSummary $customer,
        public ?AddressResponse $shippingAddress,
        public ?AddressResponse $billingAddress,
        public ?string $shortDescription,
        public ?string $privateDescription,
        public ?string $assignment,
        public ?int $adultGuests,
        public ?int $childGuests,
        public bool $callBefore,
        public array $lines,
        public ?string $createdBy,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
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
            CustomerSummary::fromEntity($note->customer),
            $note->shippingAddress !== null ? AddressResponse::fromEntity($note->shippingAddress) : null,
            $note->billingAddress !== null ? AddressResponse::fromEntity($note->billingAddress) : null,
            $note->shortDescription,
            $note->privateDescription,
            $note->assignment,
            $note->adultGuests,
            $note->childGuests,
            $note->callBefore,
            array_values(array_map(DeliveryNoteLineResponse::fromEntity(...), $note->lines->toArray())),
            $note->createdBy?->displayName,
            $note->createdAt,
            $note->updatedAt,
        );
    }
}
