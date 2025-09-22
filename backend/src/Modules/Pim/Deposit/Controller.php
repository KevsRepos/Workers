<?php

namespace App\Modules\Pim\Deposit;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Modules\Pim\Deposit\Dto\CreateDepositRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Modules\Pim\Deposit\Service;

class Controller extends AbstractController
{
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
