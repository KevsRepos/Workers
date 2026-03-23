<?php

namespace App\Modules\Pim\ProductUnion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Modules\Pim\ProductUnion\Dto\CreateProductUnionRequestDto;
use App\Modules\Pim\ProductUnion\Dto\UpdateProductUnionRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/product-unions', methods: ['GET'])]
    public function list(Service $service): JsonResponse
    {
        return $this->json($service->list());
    }

    #[Route('/product-unions', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateProductUnionRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->save($dto);

        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }

        return new JsonResponse($result->getResponse(), 201);
    }

    #[Route('/product-unions/{id}', methods: ['GET'])]
    public function getById(string $id, Service $service): JsonResponse
    {
        $result = $service->getById($id);
        if (!$result) {
            return new JsonResponse(['error' => 'ProductUnion not found'], 404);
        }
        return $this->json($result);
    }

    #[Route('/product-unions/{id}', methods: ['PUT'])]
    public function update(
        string $id,
        #[MapRequestPayload] UpdateProductUnionRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->update($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getMessage(), 200);
    }
}
