<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Customer;

final readonly class CustomerResponse
{
    /**
     * @param list<AddressResponse> $addresses
     */
    public function __construct(
        public string $id,
        public bool $company,
        public ?string $firstName,
        public ?string $surname,
        public ?string $companyName,
        public string $displayName,
        public ?string $email,
        public ?string $phone,
        public ?string $defaultShippingAddressId,
        public ?string $defaultBillingAddressId,
        public array $addresses,
        public bool $anonymized,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Customer $customer): self
    {
        return new self(
            (string) $customer->id,
            $customer->company,
            $customer->firstName,
            $customer->surname,
            $customer->companyName,
            $customer->displayName,
            $customer->email,
            $customer->phone,
            $customer->defaultShippingAddress?->id,
            $customer->defaultBillingAddress?->id,
            array_values(array_map(AddressResponse::fromEntity(...), $customer->addresses->toArray())),
            $customer->anonymizedAt !== null,
            $customer->createdAt,
            $customer->updatedAt,
        );
    }
}
