<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Constraint\PersonOrCompany;
use Symfony\Component\Validator\Constraints as Assert;

#[PersonOrCompany]
class CustomerData
{
    public bool $company = false;

    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[Assert\Length(max: 100)]
    public ?string $surname = null;

    #[Assert\Length(max: 255)]
    public ?string $companyName = null;

    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_HTML5)]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 50)]
    #[Assert\Regex(pattern: '/^[0-9+()\/\-.\s]*$/', message: 'Phone number contains invalid characters.')]
    public ?string $phone = null;
}
