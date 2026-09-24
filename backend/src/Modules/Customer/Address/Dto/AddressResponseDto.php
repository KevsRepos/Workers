<?php declare(strict_types=1);

namespace App\Modules\Customer\Address\Dto;

readonly final class AddressResponseDto
{
    public function __construct(
        public ?string $id,
        public ?string $street,
        public ?string $houseNumber,
        public ?string $postalCode,
        public ?string $city,
        public ?string $country,
        public bool $defaultShippingAddress,
        public bool $defaultBillingAddress
    ) {}
}