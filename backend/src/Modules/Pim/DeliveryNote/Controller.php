<?php

namespace App\Modules\Pim\DeliveryNote;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Pim\DeliveryNote\Service;
use App\Modules\Pim\DeliveryNote\Dto\CreateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\UpdateDeliveryNoteRequestDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class Controller extends AbstractController
{
    #[Route('/delivery-notes/{status}', methods: ['GET'])]
    public function listDeliveryNotes(string $status, Service $service): JsonResponse
    {
        $deliveryNotes = $service->listByStatus((int)$status);
        return $this->json($deliveryNotes);
    }

    #[Route('/delivery-note/{id}', methods: ['GET'])]
    public function getDeliveryNoteById(string $id, Service $service): JsonResponse
    {
        $deliveryNote = $service->getById($id);
        if (!$deliveryNote) {
            return new JsonResponse(['error' => 'DeliveryNote not found'], 404);
        }
        return $this->json($deliveryNote);
    }

    /*
     * Example JSON:
     * {
     *     "customerId": "...",
     *     "deliveryDate": "2026-03-10",
     *     "delivery": true,
     *     "deliveryNoteProducts": [
     *         { "productId": "0x01997d1bd1427b94aacc3bc36204e04c", "quantity": 1 },
     *         { "productId": "0x01997d1bd14470f1a9594c0b9a9fb1fa", "quantity": 2 },
     *         { "productId": "0x01997d1bd1457858b5eb9a2784cfa04b", "quantity": 3 }
     *     ]
     * }
     */
    #[Route('/delivery-notes', methods: ['POST'])]
    public function createDeliveryNote(
        #[MapRequestPayload] CreateDeliveryNoteRequestDto $dto,
        Service $service
    ): JsonResponse
    {
        $result = $service->save($dto);
        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }

        return new JsonResponse($result->getResponse(), 201);
    }

    #[Route('/delivery-notes/{id}', methods: ['PUT'])]
    public function updateDeliveryNote(#[MapRequestPayload] UpdateDeliveryNoteRequestDto $dto, string $id, Service $service): JsonResponse
    {
        $result = $service->update($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse($result, $result->getCode());
        }

        return new JsonResponse($result->getResponse());
    }

    #[Route('/delivery-notes/{id}', methods: ['DELETE'])]
    public function deleteDeliveryNote(string $id): JsonResponse
    {
        // TODO: Delete delivery note by $id
        return $this->json(['status' => 'deleted', 'id' => $id]);
    }
}
