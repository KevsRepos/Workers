<?php

namespace App\Modules\Customer\Address;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Modules\Customer\Address\Service;
use App\Modules\Customer\Address\Dto\CreateCustomerAddressRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/customer-addresses', methods: ['GET'])]
    public function listAddresses(): JsonResponse
    {
        // placeholder; implement repository listing if required
        $addresses = [];
        return $this->json($addresses);
    }

    #[Route('/customer-addresses', methods: ['POST'])]
    public function createAddress(
        #[MapRequestPayload] CreateCustomerAddressRequestDto $dto,
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
