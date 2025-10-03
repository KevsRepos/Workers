<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use App\Modules\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;

class Factory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function create(
        string $street,
        string $houseNumber,
        string $postalCode,
        string $city,
        ?string $country,
        bool $isPrimary,
        string $customerId
    ): CustomerAddress {
        $address = new CustomerAddress();
        $address->street = $street;
        $address->houseNumber = $houseNumber;
        $address->postalCode = $postalCode;
        $address->city = $city;
        $address->country = $country;
        $address->isPrimary = $isPrimary;

        // Find Customer entity by raw ID
        $address->customer = $customerId
            ? $this->em->getRepository(Customer::class)->find($customerId)
            : null;

        return $address;
    }
}
