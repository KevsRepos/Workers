<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\CreateOrderPresetRequestDto;
use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\UpdateOrderPresetRequestDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class Controller extends AbstractController
{
    #[Route('/order-presets', methods: ['GET'])]
    public function list(Service $service): JsonResponse
    {
        return $this->json($service->list());
    }

    #[Route('/order-presets', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateOrderPresetRequestDto $dto,
        Service $service,
    ): JsonResponse {
        $result = $service->save($dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }

        return new JsonResponse($result->getResponse(), 201);
    }

    #[Route('/order-presets/{id}', methods: ['GET'])]
    public function getById(string $id, Service $service): JsonResponse
    {
        $orderPreset = $service->getById($id);
        if (!$orderPreset) {
            return new JsonResponse(['error' => 'OrderPreset not found'], 404);
        }

        return $this->json($orderPreset);
    }

    #[Route('/order-presets/{id}', methods: ['PUT'])]
    public function update(
        string $id,
        #[MapRequestPayload] UpdateOrderPresetRequestDto $dto,
        Service $service,
    ): JsonResponse {
        $result = $service->update($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }

        return new JsonResponse($result->getResponse());
    }

    #[Route('/order-presets/{id}', methods: ['DELETE'])]
    public function delete(string $id, Service $service): JsonResponse
    {
        $result = $service->delete($id);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }

        return new JsonResponse($result->getResponse());
    }
}