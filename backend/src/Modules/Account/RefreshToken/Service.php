<?php declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Modules\Account\RefreshToken\Repository;
use App\Modules\Account\Service as AccountService;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface as JWTManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;

final class Service {
    public function __construct(

        private Repository $repo, 
        private AccountService $accountService,
        private JWTManager $jwtManager,
    ){}

    public function logUserOut(Request $request): true
    {
        $cookieRefreshToken = $request->cookies->get('refresh_token');

        if(!$cookieRefreshToken) {
            throw new \Exception('No refresh token found in cookies');
        }

        $currentRefreshToken = $this->retrieveRefreshToken($cookieRefreshToken);

        $this->deleteOldToken($currentRefreshToken);

        return true;
    }

    public function retrieveRefreshToken(string $token): ?RefreshToken
    {
        return $this->repo->findByToken($token);
    }

    private function deleteOldToken(RefreshToken $oldToken): void
    {
        $this->repo->remove($oldToken, true);
    }


    public function createRefreshToken(Request $request, Response $response): string
    {
        $cookieRefreshToken = $request->cookies->get('refresh_token');

        if(!$cookieRefreshToken) {
            throw new \Exception('No refresh token found in cookies');
        }

        $currentRefreshToken = $this->retrieveRefreshToken($cookieRefreshToken);

        if(!$currentRefreshToken) {
            throw new \Exception('Invalid refresh token');
        }

        $token = $this->saveRefreshToken($currentRefreshToken->account->id);

        $this->deleteOldToken($currentRefreshToken);

        $this->setRefreshTokenInHttpOnlyCookie($response, $token);

        $this->createJWTHttpOnlyCookie($response, $currentRefreshToken->account);

        return $token;
    }
    
    public function saveRefreshToken(string $accountId): string
    {
        $token = bin2hex(random_bytes(32));
        
        $refreshToken = new RefreshToken();

        $refreshToken->account = $this->accountService->getAccountById($accountId);

        $refreshToken->token =  $token;

        $refreshToken->expires = new DateTimeImmutable('+7 days');

        $this->repo->save($refreshToken, true);

        return $token;
    }

    public function setRefreshTokenInHttpOnlyCookie(Response $response, string $refreshToken)
    {
        // Implementation for setting the refresh token in an HTTP-only cookie
        $response->headers->setCookie(
            Cookie::create('refresh_token')
                ->withValue($refreshToken)
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
                ->withPath('/')
                ->withExpires(new \DateTimeImmutable('+7 days'))
        );
    }

    public function createJWTHttpOnlyCookie(Response $response, UserInterface $user): Response
    {
        $accessToken = $this->jwtManager->create($user);

        $response->headers->setCookie(
            Cookie::create('jwt_token')
                ->withValue($accessToken)
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite('lax')
                ->withPath('/')
        );

        return $response;
    }
}