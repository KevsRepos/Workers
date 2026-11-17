<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

use App\Modules\Account\Account;

final readonly class AccountResponse
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public string $id,
        public string $emailAddress,
        public string $firstName,
        public string $surname,
        public string $displayName,
        public array $roles,
        public bool $active,
        public ?\DateTimeImmutable $lastLoginAt,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Account $account): self
    {
        return new self(
            (string) $account->id,
            $account->emailAddress,
            $account->firstName,
            $account->surname,
            $account->displayName,
            $account->getRoles(),
            $account->active,
            $account->lastLoginAt,
            $account->createdAt,
            $account->updatedAt,
        );
    }
}
