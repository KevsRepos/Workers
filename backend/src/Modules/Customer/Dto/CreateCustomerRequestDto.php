<?php declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomerRequestDto
{
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\NotBlank]
    public string $surName;
}
