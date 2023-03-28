<?php declare(strict_types=1);

namespace App\Modules\Account\DTO;

use DateTimeImmutable;

readonly final class SendAccountRequest {
    public function __construct(
        public string $emailAddress,
        public string $firstName,
        public string $surname,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ){}
}