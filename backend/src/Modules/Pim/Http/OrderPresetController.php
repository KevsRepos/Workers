<?php

declare(strict_types=1);

namespace App\Modules\Pim\Http;

use App\Modules\Account\Role;
use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\OrderPresetData;
use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\OrderPresetResponse;
use App\Modules\Pim\DeliveryNote\OrderPreset\OrderPresetService;
use App\Shared\Http\ApiController;
use App\Shared\Http\Requirement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order-presets', name: 'order_presets_', requirements: ['id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class OrderPresetController extends ApiController
{
    public function __construct(private readonly OrderPresetService $presets)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_map(OrderPresetResponse::fromEntity(...), $this->presets->list()));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(OrderPresetResponse::fromEntity($this->presets->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] OrderPresetData $payload): JsonResponse
    {
        $preset = $this->presets->create($payload);

        return $this->persisted(static fn () => OrderPresetResponse::fromEntity($preset), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] OrderPresetData $payload): JsonResponse
    {
        $preset = $this->presets->update($this->presets->get($id), $payload);

        return $this->persisted(static fn () => OrderPresetResponse::fromEntity($preset));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        $this->presets->delete($this->presets->get($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
