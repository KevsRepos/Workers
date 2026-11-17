<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Builds RFC 9457 responses for cases handled without throwing (e.g. inside security handlers).
 */
final class ProblemResponse
{
    /**
     * @param array<string, mixed> $extensions
     */
    public static function create(Request $request, int $status, string $type, string $title, string $detail, array $extensions = []): JsonResponse
    {
        $body = [
            'type' => 'urn:workers:problem:' . $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getPathInfo(),
        ];

        $requestId = $request->attributes->get(RequestIdSubscriber::ATTRIBUTE);
        if (\is_string($requestId)) {
            $body['requestId'] = $requestId;
        }

        $response = new JsonResponse($body + $extensions, $status);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }
}
