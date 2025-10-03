<?php declare(strict_types=1);

namespace App\Modules\Customer\Address\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomerAddressRequestDto
{
    #[Assert\NotBlank]
    public string $street;

    #[Assert\NotBlank]
    public string $houseNumber;

    #[Assert\NotBlank]
    public string $postalCode;

    #[Assert\NotBlank]
    public string $city;

    public ?string $country = null;

    public ?bool $isPrimary = false;

    #[Assert\NotBlank]
    public string $customerId;
}
