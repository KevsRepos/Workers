<?php

declare(strict_types=1);

namespace App\Shared\Health;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Readiness probe for container orchestration and monitoring. Exposes no internals.
 */
final readonly class HealthController
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1')->fetchOne();
            $database = 'ok';
        } catch (\Throwable $e) {
            $this->logger->error('Health check: database unavailable', ['exception' => $e]);
            $database = 'unavailable';
        }

        $healthy = $database === 'ok';

        return new JsonResponse(['status' => $healthy ? 'ok' : 'degraded', 'database' => $database], $healthy ? 200 : 503);
    }
}
