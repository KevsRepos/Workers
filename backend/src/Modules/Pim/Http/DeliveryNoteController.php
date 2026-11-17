<?php

declare(strict_types=1);

namespace App\Modules\Pim\Http;

use App\Modules\Account\Role;
use App\Modules\Pim\DeliveryNote\DeliveryNoteService;
use App\Modules\Pim\DeliveryNote\DeliveryNoteStatus;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteData;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteResponse;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteSummary;
use App\Modules\Pim\DeliveryNote\Dto\QuantityUpdateRequest;
use App\Modules\Pim\DeliveryNote\Dto\ReturnNoteRequest;
use App\Modules\Pim\DeliveryNote\Dto\StatusChangeRequest;
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
use Symfony\Component\Uid\Uuid;

/**
 * Delivery notes are never deleted (GoBD); use the status "canceled" instead.
 */
#[Route('/delivery-notes', name: 'delivery_notes_', requirements: ['id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class DeliveryNoteController extends ApiController
{
    public function __construct(private readonly DeliveryNoteService $notes)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = PageRequest::fromRequest($request, defaultLimit: 25, maxLimit: 100);
        $status = null;
        if ($request->query->has('status') && $request->query->getString('status') !== '') {
            $status = DeliveryNoteStatus::tryFrom($request->query->getInt('status'))
                ?? throw new BadRequestException('Unknown status.');
        }

        [$items, $total] = $this->notes->search(
            $status,
            $request->query->getString('q') ?: null,
            self::date($request->query->getString('from')),
            self::date($request->query->getString('to')),
            $page->offset(),
            $page->limit,
            self::uuid($request->query->getString('customerId')),
        );

        return $this->json(new PageResponse(array_map(DeliveryNoteSummary::fromEntity(...), $items), $page->page, $page->limit, $total));
    }

    #[Route('/status-counts', name: 'status_counts', methods: ['GET'])]
    public function statusCounts(): JsonResponse
    {
        return $this->json($this->notes->countByStatus());
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(DeliveryNoteResponse::fromEntity($this->notes->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] DeliveryNoteData $payload): JsonResponse
    {
        $note = $this->notes->create($payload);

        return $this->persisted(static fn () => DeliveryNoteResponse::fromEntity($note), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] DeliveryNoteData $payload): JsonResponse
    {
        $note = $this->notes->update($this->notes->get($id), $payload);

        return $this->persisted(static fn () => DeliveryNoteResponse::fromEntity($note));
    }

    #[Route('/{id}/status', name: 'status', methods: ['PUT'])]
    public function changeStatus(string $id, #[MapRequestPayload] StatusChangeRequest $payload): JsonResponse
    {
        \assert($payload->status !== null);

        $note = $this->notes->changeStatus($this->notes->get($id), $payload->status);

        return $this->persisted(static fn () => DeliveryNoteResponse::fromEntity($note));
    }

    #[Route('/{id}/quantities', name: 'quantities', methods: ['PUT'])]
    public function updateQuantities(string $id, #[MapRequestPayload] QuantityUpdateRequest $payload): JsonResponse
    {
        $note = $this->notes->updateQuantities($this->notes->get($id), $payload);

        return $this->persisted(static fn () => DeliveryNoteResponse::fromEntity($note));
    }

    #[Route('/{id}/return-groups', name: 'return_groups', methods: ['GET'])]
    public function returnGroups(string $id): JsonResponse
    {
        $note = $this->notes->get($id);

        return $this->json(['deliveryNote' => DeliveryNoteResponse::fromEntity($note), 'groups' => $this->notes->returnGroups($note)]);
    }

    #[Route('/{id}/return-note', name: 'return_note', methods: ['PUT'])]
    public function saveReturnNote(string $id, #[MapRequestPayload] ReturnNoteRequest $payload): JsonResponse
    {
        $note = $this->notes->saveReturnNote($this->notes->get($id), $payload);

        return $this->persisted(static fn () => DeliveryNoteResponse::fromEntity($note));
    }

    private static function uuid(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        return Uuid::isValid($value) ? $value : throw new BadRequestException('Invalid customer id.');
    }

    private static function date(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false ? $date : throw new BadRequestException('Dates must use the format YYYY-MM-DD.');
    }
}
