<?php declare(strict_types=1);

namespace App\Modules\Customer;

use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use App\Modules\Customer\Address\CustomerAddress;

class Factory
{
    public function create(CreateCustomerRequestDto $dto): Customer
    {
        $customer = new Customer();
        $customer->company = !empty($dto->companyName);
        $customer->firstName = $dto->firstName;
        $customer->surname = $dto->surname;
        $customer->companyName = $dto->companyName;
        $customer->email = $dto->email;
        $customer->phone = $dto->phone;

        foreach ($dto->addresses as $index => $addressDto) {

            $address = new CustomerAddress();

            if ($addressDto->standardShippingAddress) {
                $customer->defaultShippingAddress = $address;
            }

            if ($addressDto->standardBillingAddress) {
                $customer->defaultBillingAddress = $address;
            }

            $address->street = $addressDto->street;
            $address->houseNumber = $addressDto->houseNumber;
            $address->postalCode = $addressDto->postalCode;
            $address->city = $addressDto->city;
            $address->country = $addressDto->country;
            $address->customer = $customer;

            $customer->addresses->add($address);
        }

        return $customer;
    }
}
