<?php declare(strict_types=1);

namespace App\Modules\Customer;

class Factory
{
    public function create(string $firstName, string $surname): Customer
    {
        $customer = new Customer();
        $customer->firstName = $firstName;
        $customer->surname = $surname;

        return $customer;
    }
}
