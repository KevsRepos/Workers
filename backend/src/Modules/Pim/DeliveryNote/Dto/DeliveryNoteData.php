<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    'this.pickupDate === null or this.pickupDate >= this.deliveryDate',
    message: 'The pickup date must not be before the delivery date.',
)]
final class DeliveryNoteData
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $customerId = '';

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $deliveryDate = '';

    #[Assert\Date]
    public ?string $pickupDate = null;

    public bool $delivery = false;

    #[Assert\Length(max: 5000)]
    public ?string $shortDescription = null;

    #[Assert\Length(max: 5000)]
    public ?string $privateDescription = null;

    #[Assert\Length(max: 255)]
    public ?string $assignment = null;

    #[Assert\Uuid]
    public ?string $shippingAddressId = null;

    #[Assert\Uuid]
    public ?string $billingAddressId = null;

    #[Assert\PositiveOrZero]
    #[Assert\LessThan(100000)]
    public ?int $adultGuests = null;

    #[Assert\PositiveOrZero]
    #[Assert\LessThan(100000)]
    public ?int $childGuests = null;

    public bool $callBefore = false;

    /** @var list<DeliveryNoteLineData> */
    #[Assert\Count(min: 1, minMessage: 'Add at least one product.', max: 500)]
    #[Assert\Valid]
    public array $lines = [];
}
