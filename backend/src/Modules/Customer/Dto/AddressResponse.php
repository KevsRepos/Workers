<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Address\CustomerAddress;

final readonly class AddressResponse
{
    public function __construct(
        public string $id,
        public string $street,
        public string $houseNumber,
        public string $postalCode,
        public string $city,
        public ?string $country,
        public ?string $note,
        public string $singleLine,
        public bool $defaultShipping,
        public bool $defaultBilling,
    ) {
    }

    public static function fromEntity(CustomerAddress $address): self
    {
        $customer = $address->customer;

        return new self(
            (string) $address->id,
            $address->street,
            $address->houseNumber,
            $address->postalCode,
            $address->city,
            $address->country,
            $address->note,
            $address->singleLine,
            $customer->defaultShippingAddress === $address,
            $customer->defaultBillingAddress === $address,
        );
    }
}
