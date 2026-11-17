<?php

declare(strict_types=1);

namespace App\Modules\Pim\Http;

use App\Modules\Account\Role;
use App\Modules\Pim\Product\Dto\ProductData;
use App\Modules\Pim\Product\Dto\ProductResponse;
use App\Modules\Pim\Product\ProductListFilter;
use App\Modules\Pim\Product\ProductService;
use App\Shared\Exception\BadRequestException;
use App\Shared\Http\ApiController;
use App\Shared\Http\PageRequest;
use App\Shared\Http\PageResponse;
use App\Shared\Http\Requirement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products', name: 'products_', requirements: ['id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class ProductController extends ApiController
{
    public function __construct(private readonly ProductService $products)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = PageRequest::fromRequest($request, defaultLimit: 25, maxLimit: 100);
        $filter = ProductListFilter::tryFrom($request->query->getString('filter', 'all') ?: 'all')
            ?? throw new BadRequestException('Unknown filter.');

        [$items, $total] = $this->products->search($request->query->getString('q') ?: null, $filter, $page->offset(), $page->limit);

        return $this->json(new PageResponse(array_map(ProductResponse::fromEntity(...), $items), $page->page, $page->limit, $total));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(ProductResponse::fromEntity($this->products->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(Role::ADMIN)]
    public function create(#[MapRequestPayload] ProductData $payload): JsonResponse
    {
        $product = $this->products->create($payload);

        return $this->persisted(static fn () => ProductResponse::fromEntity($product), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted(Role::ADMIN)]
    public function update(string $id, #[MapRequestPayload] ProductData $payload): JsonResponse
    {
        $product = $this->products->update($this->products->get($id), $payload);

        return $this->persisted(static fn () => ProductResponse::fromEntity($product));
    }
}
