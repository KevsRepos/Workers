<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Customer;

final readonly class CustomerSummary
{
    public function __construct(
        public string $id,
        public bool $company,
        public string $displayName,
        public ?string $email,
        public ?string $phone,
        public ?string $defaultAddress,
    ) {
    }

    public static function fromEntity(Customer $customer): self
    {
        $address = $customer->defaultShippingAddress ?? $customer->defaultBillingAddress;

        return new self(
            (string) $customer->id,
            $customer->company,
            $customer->displayName,
            $customer->email,
            $customer->phone,
            $address?->singleLine,
        );
    }
}
