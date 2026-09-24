<?php

namespace App\Modules\Customer\Address;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Modules\Customer\Address\Service;
use App\Modules\Customer\Address\Dto\CreateCustomerAddressRequestDto;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/customer-addresses', methods: ['GET'])]
    public function show(Service $service): JsonResponse
    {
        $addresses = $service->listAddresses();
        return $this->json($addresses);
    }

    #[Route('/customers/{customerId}/addresses', methods: ['GET'])]
    public function listCustomerAddresses(Service $service, string $customerId): JsonResponse
    {
        $addresses = $service->listAddressesByCustomerId($customerId);
        return $this->json($addresses);
    }

    #[Route('/customer-addresses/{id}', methods: ['GET'])]
    public function showAddress(Service $service, string $id): JsonResponse
    {
        $address = $service->getAddress($id);
        return $this->json($address);
    }

    #[Route('/customer-address/{customerId}', methods: ['POST'])]
    public function createAddress(
        string $customerId,
        #[MapRequestPayload] CreateCustomerAddressRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->save($customerId, $dto);
        
        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }
        return new JsonResponse($result->getResponse(), 201);
    }
}
