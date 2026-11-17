<?php

declare(strict_types=1);

namespace App\Modules\Account\Security;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Shared\Http\ProblemResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Answers failed logins with a uniform message (no user enumeration) and records them.
 */
final readonly class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(private AuditTrail $auditTrail)
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $attemptedEmail = \is_array($payload) && \is_string($payload['email'] ?? null) ? mb_substr(mb_strtolower(trim($payload['email'])), 0, 180) : null;

        if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
            $this->auditTrail->record(AuditAction::AUTH_LOGIN_THROTTLED, data: ['email' => $attemptedEmail]);

            $response = ProblemResponse::create($request, 429, 'too-many-requests', 'Too many login attempts', 'Too many failed login attempts. Please wait a few minutes and try again.');
            $response->headers->set('Retry-After', '900');

            return $response;
        }

        $this->auditTrail->record(AuditAction::AUTH_LOGIN_FAILED, data: ['email' => $attemptedEmail, 'reason' => $exception->getMessageKey()]);

        return ProblemResponse::create($request, 401, 'invalid-credentials', 'Login failed', 'Email address or password is incorrect, or the account is disabled.');
    }
}
