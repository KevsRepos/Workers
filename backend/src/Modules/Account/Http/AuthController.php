<?php

declare(strict_types=1);

namespace App\Modules\Account\Http;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Modules\Account\AccountService;
use App\Modules\Account\Dto\AccountResponse;
use App\Modules\Account\Dto\ChangePasswordRequest;
use App\Modules\Account\Dto\RefreshRequest;
use App\Modules\Account\RefreshToken\RefreshTokenService;
use App\Modules\Account\Security\TokenIssuer;
use App\Shared\Http\ProblemResponse;
use App\Shared\Security\CurrentUser;
use App\Shared\Http\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[Route('/auth', name: 'auth_')]
final class AuthController extends ApiController
{
    /**
     * Handled by the json_login authenticator; this method is never executed.
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Handled by the security firewall.');
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(#[MapRequestPayload] RefreshRequest $payload, Request $request, RefreshTokenService $refreshTokens, TokenIssuer $tokenIssuer): JsonResponse
    {
        try {
            [$plain, $token] = $refreshTokens->rotate($payload->refreshToken);
        } catch (AuthenticationException) {
            // Returned (not thrown) so that a reuse-triggered family revocation is committed.
            return ProblemResponse::create($request, 401, 'invalid-refresh-token', 'Session expired', 'The session is no longer valid. Please sign in again.');
        }

        return $this->json($tokenIssuer->issue($token->account, $plain, $token));
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(#[MapRequestPayload] RefreshRequest $payload, RefreshTokenService $refreshTokens, AuditTrail $auditTrail): Response
    {
        $account = $refreshTokens->revokeSession($payload->refreshToken);
        if ($account !== null) {
            $auditTrail->record(AuditAction::AUTH_LOGOUT, subjectType: 'Account', subjectId: $account->id, actor: $account);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(CurrentUser $currentUser): JsonResponse
    {
        return $this->json(AccountResponse::fromEntity($currentUser->account()));
    }

    #[Route('/change-password', name: 'change_password', methods: ['POST'])]
    public function changePassword(#[MapRequestPayload] ChangePasswordRequest $payload, CurrentUser $currentUser, AccountService $accounts): Response
    {
        $accounts->changeOwnPassword($currentUser->account(), $payload->currentPassword, $payload->newPassword, $payload->refreshToken);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
