<?php declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Address\Dto\AddressResponseDto;

readonly final class CustomerResponseDto
{
    public function __construct(
        public ?string $id,
        public ?string $firstName,
        public ?string $surname,
        public ?string $email,
        public ?string $phoneNumber,
        public ?AddressResponseDto $defaultShippingAddress,
        public ?AddressResponseDto $defaultBillingAddress,
        public array $addresses = []
    ) {}
}