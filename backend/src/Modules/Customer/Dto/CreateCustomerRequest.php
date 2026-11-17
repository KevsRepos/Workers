<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateCustomerRequest extends CustomerData
{
    /** @var list<AddressData> */
    #[Assert\Valid]
    #[Assert\Count(max: 20)]
    public array $addresses = [];
}
