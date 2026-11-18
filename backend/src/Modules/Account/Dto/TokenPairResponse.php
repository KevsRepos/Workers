<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

final readonly class TokenPairResponse
{
    public function __construct(
        public string $accessToken,
        public \DateTimeImmutable $accessTokenExpiresAt,
        public string $refreshToken,
        public \DateTimeImmutable $refreshTokenExpiresAt,
        public AccountResponse $account,
    ) {
    }
}
