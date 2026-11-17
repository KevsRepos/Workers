<?php

declare(strict_types=1);

namespace App\Shared\Maintenance;

use App\Modules\Account\RefreshToken\RefreshTokenService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DailyMaintenanceHandler
{
    public function __construct(
        private RefreshTokenService $refreshTokens,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DailyMaintenanceMessage $message): void
    {
        $deleted = $this->refreshTokens->purgeExpired();
        $this->logger->info('Daily maintenance: purged {count} refresh tokens', ['count' => $deleted]);
    }
}
