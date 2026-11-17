<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use App\Shared\Http\RequestContext;
use App\Shared\Security\CurrentUser;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Enriches every log record with correlation id and acting user id for traceability.
 */
#[AsMonologProcessor]
final readonly class RequestContextProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestContext $requestContext,
        private CurrentUser $currentUser,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        if (($requestId = $this->requestContext->getRequestId()) !== null) {
            $extra['request_id'] = $requestId;
        }

        if (($userId = $this->currentUser->idOrNull()) !== null) {
            $extra['user_id'] = $userId;
        }

        return $record->with(extra: $extra);
    }
}
