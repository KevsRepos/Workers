<?php declare(strict_types=1);

namespace App\Lib;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JWTExtractor {
    public function __construct(private TokenStorageInterface $tokenStorage, private JWTTokenManagerInterface $jwtManager)
    {}

    public function getUserId(): int
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());

        return $decodedJwtToken['id'];
    }
}