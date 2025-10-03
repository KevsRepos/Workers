<?php

namespace App\Modules\Pim\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Pim\Product\Service;
use App\Modules\Pim\Product\Dto\CreateProductRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        // TODO: Fetch products from database
        $products = [];
        return $this->json($products);
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function getProductById(string $id): JsonResponse
    {
        // TODO: Fetch product by $id from database
        $product = null;
        return $this->json($product);
    }

    #[Route('/products', methods: ['POST'])]
    public function createProduct(
        #[MapRequestPayload] CreateProductRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->save($dto);
        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }
        return new JsonResponse($result->getMessage(), 201);
    }

    #[Route('/products/{id}', methods: ['PUT'])]
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        // TODO: Update product by $id with request data
        $data = json_decode($request->getContent(), true);
        // $product = ...
        return $this->json(['status' => 'updated', 'id' => $id, 'data' => $data]);
    }

    #[Route('/products/{id}', methods: ['DELETE'])]
    public function deleteProduct(string $id): JsonResponse
    {
        // TODO: Delete product by $id
        return $this->json(['status' => 'deleted', 'id' => $id]);
    }
}
