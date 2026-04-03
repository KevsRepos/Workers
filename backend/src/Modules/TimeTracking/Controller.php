<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Modules\TimeTracking\Dto\CreateMonthlyTimeSheetRequestDto;
use App\Modules\TimeTracking\Dto\CreateMonthlyTimeSheetEntryRequestDto;
use App\Modules\TimeTracking\Dto\UpdateMonthlyTimeSheetEntryRequestDto;

class Controller extends AbstractController
{
    #[Route('/time-sheets', methods: ['POST'])]
    public function createTimeSheet(
        #[MapRequestPayload] CreateMonthlyTimeSheetRequestDto $dto,
        Service $service
    ): JsonResponse {
        $result = $service->createTimeSheet($dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getResponse(), 201);
    }

    #[Route('/time-sheets/current', methods: ['GET'])]
    public function getCurrentTimeSheet(Service $service): JsonResponse
    {
        $timeSheet = $service->getCurrentTimeSheet();

        return $this->json($timeSheet);
    }

    #[Route('/time-sheets/{year}', methods: ['GET'], requirements: ['year' => '\d+'])]
    public function listByYear(int $year, Service $service): JsonResponse
    {
        return $this->json($service->listByYear($year));
    }

    #[Route('/time-sheets/detail/{id}', methods: ['GET'])]
    public function getTimeSheet(string $id, Service $service): JsonResponse
    {
        $timeSheet = $service->getTimeSheet($id);
        if (!$timeSheet) {
            return new JsonResponse(['error' => 'TimeSheetNotFound'], 404);
        }

        return new JsonResponse($timeSheet);
    }

    #[Route('/time-sheets/{id}/entries', methods: ['POST'])]
    public function createEntry(
        string $id,
        #[MapRequestPayload] CreateMonthlyTimeSheetEntryRequestDto $dto,
        Service $service
    ): JsonResponse {
        $result = $service->createEntry($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getResponse(), 201);
    }

    #[Route('/time-sheets/entries/{id}', methods: ['PUT'])]
    public function updateEntry(
        string $id,
        #[MapRequestPayload] UpdateMonthlyTimeSheetEntryRequestDto $dto,
        Service $service
    ): JsonResponse {
        $result = $service->updateEntry($id, $dto);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getMessage(), 200);
    }

    #[Route('/time-sheets/entries/{id}', methods: ['DELETE'])]
    public function deleteEntry(string $id, Service $service): JsonResponse
    {
        $result = $service->deleteEntry($id);
        if ($result instanceof \Error) {
            return new JsonResponse(['error' => $result->getMessage()], $result->getCode());
        }
        return new JsonResponse($result->getMessage(), 200);
    }
}
