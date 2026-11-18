<?php

declare(strict_types=1);

namespace App\Modules\Account\Security;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Modules\Account\Account;
use App\Modules\Account\RefreshToken\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RefreshTokenService $refreshTokens,
        private TokenIssuer $tokenIssuer,
        private EntityManagerInterface $entityManager,
        private AuditTrail $auditTrail,
        private ClockInterface $clock,
        private SerializerInterface $serializer,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $account = $token->getUser();
        \assert($account instanceof Account);

        $pair = $this->entityManager->wrapInTransaction(function () use ($account) {
            $account->lastLoginAt = $this->clock->now();
            [$plain, $refreshToken] = $this->refreshTokens->issue($account);
            $this->auditTrail->record(AuditAction::AUTH_LOGIN_SUCCEEDED, subjectType: 'Account', subjectId: $account->id, actor: $account);

            return $this->tokenIssuer->issue($account, $plain, $refreshToken);
        });

        return JsonResponse::fromJsonString($this->serializer->serialize($pair, 'json'));
    }
}
