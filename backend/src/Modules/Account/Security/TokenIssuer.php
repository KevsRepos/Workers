<?php

declare(strict_types=1);

namespace App\Modules\Account\Security;

use App\Modules\Account\Account;
use App\Modules\Account\Dto\AccountResponse;
use App\Modules\Account\Dto\TokenPairResponse;
use App\Modules\Account\RefreshToken\RefreshToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates the access/refresh token pair returned to the BFF.
 */
final readonly class TokenIssuer
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private ClockInterface $clock,
        #[Autowire('%lexik_jwt_authentication.token_ttl%')]
        private int $accessTokenTtl,
    ) {
    }

    public function issue(Account $account, string $plainRefreshToken, RefreshToken $refreshToken): TokenPairResponse
    {
        return new TokenPairResponse(
            $this->jwtManager->create($account),
            $this->clock->now()->modify(\sprintf('+%d seconds', $this->accessTokenTtl)),
            $plainRefreshToken,
            $refreshToken->expiresAt,
            AccountResponse::fromEntity($account),
        );
    }
}
