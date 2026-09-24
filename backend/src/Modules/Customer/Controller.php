<?php

namespace App\Modules\Customer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Modules\Customer\Service;
use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

use Symfony\Component\HttpFoundation\Request;

class Controller extends AbstractController
{
    #[Route('/customers', methods: ['GET'])]
    public function listCustomers(): JsonResponse
    {
        // ...existing code...
        $customers = []; // quick placeholder
        return $this->json($customers);
    }

    #[Route('/customer/{customerId}', methods: ['GET'])]
    public function getCustomer(string $customerId, Service $service): JsonResponse {
        $customer = $service->createCustomerResponse($service->findById($customerId));

        if (!$customer) {
            return $this->json([], 404);
        }
        return $this->json($customer);
    }

    #[Route('/customers/search', methods: ['GET'])]
    public function searchCustomers(Request $request, Service $service): JsonResponse {
        $query = trim($request->query->get('customerQuery', ''));
        if (strlen($query) < 2) {
            return $this->json([]);
        }
        $results = $service->search($query);

        return $this->json($results);
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

        $res = $result->getResponse();

        return new JsonResponse($res, 201);
    }
}