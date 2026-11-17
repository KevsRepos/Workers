<?php

declare(strict_types=1);

namespace App\Modules\Customer\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DefaultAddressesRequest
{
    #[Assert\Uuid]
    public ?string $shippingAddressId = null;

    #[Assert\Uuid]
    public ?string $billingAddressId = null;
}
