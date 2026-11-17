<?php

declare(strict_types=1);

namespace App\Shared\Http;

use App\Shared\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Converts every exception into an RFC 9457 "application/problem+json" response.
 *
 * Expected errors (4xx) expose their safe message. Unexpected errors (5xx) are logged with
 * full context and answered with a generic message plus the correlation id, so internals
 * (SQL, stack traces, paths) never leak to clients.
 */
final readonly class ProblemDetailsSubscriber implements EventSubscriberInterface
{
    private const string TYPE_PREFIX = 'urn:workers:problem:';

    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Run after the security firewall's exception listener (priority 1) has translated
        // authentication/authorization exceptions, but before Symfony's default error handling.
        return [KernelEvents::EXCEPTION => ['onException', -64]];
    }

    public function onException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();
        $requestId = $request->attributes->get(RequestIdSubscriber::ATTRIBUTE);

        [$status, $type, $title, $detail, $extensions, $headers] = $this->describe($throwable);

        $context = ['exception' => $throwable, 'status' => $status, 'path' => $request->getPathInfo()];
        if ($status >= 500) {
            $this->logger->error('Unhandled exception: {message}', ['message' => $throwable->getMessage()] + $context);
        } elseif ($status === 401 || $status === 403) {
            $this->logger->notice('Access rejected ({status}) on {path}', $context);
        } else {
            $this->logger->info('Client error ({status}) on {path}: {message}', ['message' => $throwable->getMessage()] + $context);
        }

        $body = [
            'type' => self::TYPE_PREFIX . $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getPathInfo(),
        ];
        if (\is_string($requestId)) {
            $body['requestId'] = $requestId;
        }
        $body += $extensions;

        if ($this->debug && $status >= 500) {
            $body['debug'] = ['class' => $throwable::class, 'message' => $throwable->getMessage()];
        }

        $response = new JsonResponse($body, $status, $headers);
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
        $event->allowCustomResponseCode();
    }

    /**
     * @return array{int, string, string, string, array<string, mixed>, array<string, string>}
     */
    private function describe(\Throwable $throwable): array
    {
        if ($throwable instanceof ApiException) {
            return [$throwable->getStatusCode(), $throwable->getType(), $throwable->getTitle(), $throwable->getMessage(), $throwable->getExtensions(), []];
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            /** @var array<string, string> $headers */
            $headers = $throwable->getHeaders();
            $previous = $throwable instanceof \Throwable ? $throwable->getPrevious() : null;

            if ($previous instanceof ValidationFailedException) {
                $errors = [];
                foreach ($previous->getViolations() as $violation) {
                    /** @var ConstraintViolationInterface $violation */
                    $errors[] = [
                        'field' => $violation->getPropertyPath(),
                        'message' => (string) $violation->getMessage(),
                        'code' => (string) $violation->getCode(),
                    ];
                }

                return [422, 'validation-failed', 'Validation failed', 'The request contains invalid data.', ['errors' => $errors], $headers];
            }

            return match (true) {
                $status === 400 => [400, 'bad-request', 'Bad request', 'The request could not be understood.', [], $headers],
                $status === 401 => [401, 'unauthenticated', 'Authentication required', 'Valid authentication is required.', [], $headers],
                $status === 403 => [403, 'forbidden', 'Forbidden', 'You are not allowed to perform this action.', [], $headers],
                $status === 404 => [404, 'not-found', 'Resource not found', 'The requested resource does not exist.', [], $headers],
                $status === 405 => [405, 'method-not-allowed', 'Method not allowed', 'The HTTP method is not supported for this resource.', [], $headers],
                $status === 415 => [415, 'unsupported-media-type', 'Unsupported media type', 'Send the request body as application/json.', [], $headers],
                $status === 422 => [422, 'validation-failed', 'Validation failed', 'The request contains invalid data.', [], $headers],
                $status === 429 => [429, 'too-many-requests', 'Too many requests', 'Too many requests. Please try again later.', [], $headers],
                $status < 500 => [$status, 'client-error', 'Client error', 'The request could not be processed.', [], $headers],
                default => [$status, 'server-error', 'Internal server error', 'An unexpected error occurred. Please contact support and provide the request id.', [], $headers],
            };
        }

        return [Response::HTTP_INTERNAL_SERVER_ERROR, 'server-error', 'Internal server error', 'An unexpected error occurred. Please contact support and provide the request id.', [], []];
    }
}
