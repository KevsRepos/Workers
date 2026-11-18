<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class AddressData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $street = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    public string $houseNumber = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    public string $postalCode = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $city = '';

    #[Assert\Length(max: 100)]
    public ?string $country = null;

    #[Assert\Length(max: 255)]
    public ?string $note = null;

    public bool $defaultShipping = false;

    public bool $defaultBilling = false;
}
