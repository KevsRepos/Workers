<?php declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use App\Modules\Customer\Constraint\PersonOrCompany;
use App\Modules\Customer\Address\Dto\CreateCustomerAddressRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

#[PersonOrCompany]
class CreateCustomerRequestDto
{
    public ?string $firstName = null;

    public ?string $surname = null;

    public ?string $companyName = null;

    #[Assert\Email]
    public ?string $email = null;

    public ?string $phone = null;

    /** @var CreateCustomerAddressRequestDto[] */
    #[Assert\Valid]
    public array $addresses = [];
}
