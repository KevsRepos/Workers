<?php

namespace App\Modules\Pim\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Pim\Product\Service;
use App\Modules\Pim\Product\Dto\CreateProductRequestDto;
use App\Modules\Pim\Product\Dto\UpdateProductRequestDto;
use App\Modules\Pim\Product\Dto\ProductDetailResponseDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/products/list/{filter}/{page}', methods: ['GET'])]
    public function listProducts(string $filter, int $page, Service $service): JsonResponse
    {
        $page = max(1, $page);
        $filterEnum = ProductListFilter::tryFrom($filter) ?? ProductListFilter::ALL;

        $result = $service->list($page, $filterEnum);

        return $this->json($result);
    }

    #[Route('/products/search', methods: ['GET'])]
    public function searchProducts(Request $request, Service $service): JsonResponse {
        $query = $request->query->get('productName', '');
        $results = $service->search($query);

        return $this->json($results);
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function getProductById(string $id, Service $service): JsonResponse
    {
        $product = $service->getById($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $response = new ProductDetailResponseDto(
            $product->id,
            $product->name,
            $product->salesPrice,
            $product->sellable,
            $product->rentable,
            $product->quantityInCrate,
            $product->deposit ? [
                'id' => $product->deposit->id,
                'singleAmount' => $product->deposit->singleAmount,
                'crateAmount' => $product->deposit->crateAmount,
            ] : null,
            $product->createdAt,
            $product->updatedAt
        );

        return new JsonResponse($response);
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
    public function updateProduct(
        string $id,
        #[MapRequestPayload] UpdateProductRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->update($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getMessage(), 200);
    }

    #[Route('/products/{id}', methods: ['DELETE'])]
    public function deleteProduct(string $id): JsonResponse
    {
        // TODO: Delete product by $id
        return $this->json(['status' => 'deleted', 'id' => $id]);
    }
}
