<?php declare(strict_types=1);

namespace App\Modules\Account;

class Factory {
    public function create(string $emailAddress, string $firstName, string $surname): Account
    {
        $account = new Account();

        $account->emailAddress = $emailAddress;
        $account->firstName = $firstName;
        $account->surname = $surname;
        
        return $account;
    }
}