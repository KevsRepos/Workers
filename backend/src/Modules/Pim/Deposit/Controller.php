<?php

namespace App\Modules\Pim\Deposit;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Modules\Pim\Deposit\Dto\CreateDepositRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Modules\Pim\Deposit\Service;

class Controller extends AbstractController
{
    #[Route('/deposits', methods: ['GET'])]
    public function list(Service $service): JsonResponse
    {
        return $this->json($service->list());
    }

    #[Route('/deposits', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateDepositRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->save($dto);
        
        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }

        return new JsonResponse($result->getMessage(), 201);
    }
}
