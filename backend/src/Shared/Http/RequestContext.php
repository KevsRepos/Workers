<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Contracts\Service\ResetInterface;

/**
 * Per-request correlation data (request id, client ip, user agent) used by logging and auditing.
 */
final class RequestContext implements ResetInterface
{
    private ?string $requestId = null;
    private ?string $clientIp = null;
    private ?string $userAgent = null;

    public function initialize(string $requestId, ?string $clientIp, ?string $userAgent): void
    {
        $this->requestId = $requestId;
        $this->clientIp = $clientIp;
        $this->userAgent = $userAgent !== null ? mb_substr($userAgent, 0, 255) : null;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function reset(): void
    {
        $this->requestId = null;
        $this->clientIp = null;
        $this->userAgent = null;
    }
}
