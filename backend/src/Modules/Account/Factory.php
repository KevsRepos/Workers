<?php declare(strict_types=1);

namespace App\Modules\Account;

use DateTimeImmutable;

class Factory {
    public function create(string $emailAddress, string $firstName, string $surname): Account
    {
        $account = new Account();

        $account
        ->setEmailAddress($emailAddress)
        ->setFirstName($firstName)
        ->setSurname($surname)
        ->setCreatedAt(new DateTimeImmutable());
        
        return $account;
    }
}