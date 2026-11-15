<?php declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Lib\Success;
use App\Modules\Account\RefreshToken\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController {
    #[Route(path: '/refresh-token', methods: [Request::METHOD_GET])]
    public function refreshToken(Service $refreshTokenService, Request $request): Response
    {
        $response = new Response();

        $refreshTokenService->createRefreshToken($request, $response);
        
        return $response;
    }

    #[Route(path: '/logout', methods: [Request::METHOD_GET])]
    public function logout(Service $service, Request $request)
    {
        try {
            $service->logUserOut($request);

            return new JsonResponse([]);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}