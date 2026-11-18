<?php

declare(strict_types=1);

namespace App\Modules\Pim\Http;

use App\Modules\Account\Role;
use App\Modules\Pim\ProductUnion\Dto\ProductUnionData;
use App\Modules\Pim\ProductUnion\Dto\ProductUnionResponse;
use App\Modules\Pim\ProductUnion\ProductUnionService;
use App\Shared\Http\ApiController;
use App\Shared\Http\Requirement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product-unions', name: 'product_unions_', requirements: ['id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class ProductUnionController extends ApiController
{
    public function __construct(private readonly ProductUnionService $unions)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_map(ProductUnionResponse::fromEntity(...), $this->unions->list()));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(ProductUnionResponse::fromEntity($this->unions->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(Role::ADMIN)]
    public function create(#[MapRequestPayload] ProductUnionData $payload): JsonResponse
    {
        $union = $this->unions->create($payload);

        return $this->persisted(static fn () => ProductUnionResponse::fromEntity($union), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted(Role::ADMIN)]
    public function update(string $id, #[MapRequestPayload] ProductUnionData $payload): JsonResponse
    {
        $union = $this->unions->update($this->unions->get($id), $payload);

        return $this->persisted(static fn () => ProductUnionResponse::fromEntity($union));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(Role::ADMIN)]
    public function delete(string $id): Response
    {
        $this->unions->delete($this->unions->get($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
