<?php declare(strict_types=1);

namespace App\Modules\Account\Create;

readonly final class AccountRequestData {
    public function __construct(
        public string $emailAddress,
        public string $firstName,
        public string $surname,
        public string $password,
    ){}
}