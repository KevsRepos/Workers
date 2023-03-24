<?php declare(strict_types=1);

namespace App\DTO;

readonly final class DataCreateAccountRequest {
    public function __construct(
        public string $emailAddress,
        public string $firstName,
        public string $surname,
        public string $password,
    ){}
}