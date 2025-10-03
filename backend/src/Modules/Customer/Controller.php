<?php

namespace App\Modules\Customer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Modules\Customer\Service;
use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/customers', methods: ['GET'])]
    public function listCustomers(): JsonResponse
    {
        // ...existing code...
        $customers = []; // quick placeholder
        return $this->json($customers);
    }

    #[Route('/customers', methods: ['POST'])]
    public function createCustomer(
        #[MapRequestPayload] CreateCustomerRequestDto $dto,
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