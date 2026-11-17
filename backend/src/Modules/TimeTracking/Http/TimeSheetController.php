<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Http;

use App\Modules\Account\Account;
use App\Modules\Account\AccountService;
use App\Modules\Account\Role;
use App\Modules\TimeTracking\Dto\EntryData;
use App\Modules\TimeTracking\Dto\EntryResponse;
use App\Modules\TimeTracking\Security\TimeSheetVoter;
use App\Modules\TimeTracking\TimeTrackingService;
use App\Shared\Http\ApiController;
use App\Shared\Http\Requirement;
use App\Shared\Security\CurrentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Own time sheets by default; administrators may pass ?accountId=… to work on another
 * employee's sheet. Every change is audited.
 */
#[Route('/time-sheets', name: 'time_sheets_', requirements: ['year' => '\d{4}', 'month' => '\d{1,2}', 'id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class TimeSheetController extends ApiController
{
    public function __construct(
        private readonly TimeTrackingService $timeTracking,
        private readonly CurrentUser $currentUser,
        private readonly AccountService $accounts,
    ) {
    }

    #[Route('/years', name: 'years', methods: ['GET'])]
    public function years(Request $request): JsonResponse
    {
        return $this->json($this->timeTracking->years($this->owner($request, TimeSheetVoter::VIEW)));
    }

    #[Route('/{year}', name: 'year', methods: ['GET'])]
    public function year(int $year, Request $request): JsonResponse
    {
        return $this->json($this->timeTracking->yearSummary($this->owner($request, TimeSheetVoter::VIEW), $year));
    }

    #[Route('/{year}/{month}', name: 'month', methods: ['GET'])]
    public function month(int $year, int $month, Request $request): JsonResponse
    {
        return $this->json($this->timeTracking->month($this->owner($request, TimeSheetVoter::VIEW), $year, $month));
    }

    #[Route('/{year}/{month}/entries', name: 'entry_create', methods: ['POST'])]
    public function createEntry(int $year, int $month, #[MapRequestPayload] EntryData $payload, Request $request): JsonResponse
    {
        $entry = $this->timeTracking->createEntry($this->owner($request, TimeSheetVoter::EDIT), $year, $month, $payload);

        return $this->persisted(fn () => EntryResponse::fromEntity($entry, $this->timeTracking->hours($entry), $this->timeTracking->warnings($entry)), Response::HTTP_CREATED);
    }

    #[Route('/entries/{id}', name: 'entry_update', methods: ['PUT'])]
    public function updateEntry(string $id, #[MapRequestPayload] EntryData $payload): JsonResponse
    {
        $entry = $this->timeTracking->getEntry($id);
        $this->denyAccessUnlessGranted(TimeSheetVoter::EDIT, $entry->timeSheet->account);
        $this->timeTracking->updateEntry($entry, $payload);

        return $this->persisted(fn () => EntryResponse::fromEntity($entry, $this->timeTracking->hours($entry), $this->timeTracking->warnings($entry)));
    }

    #[Route('/entries/{id}', name: 'entry_delete', methods: ['DELETE'])]
    public function deleteEntry(string $id): Response
    {
        $entry = $this->timeTracking->getEntry($id);
        $this->denyAccessUnlessGranted(TimeSheetVoter::EDIT, $entry->timeSheet->account);
        $this->timeTracking->deleteEntry($entry);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function owner(Request $request, string $attribute): Account
    {
        $accountId = $request->query->getString('accountId');
        $owner = $accountId !== '' && $accountId !== $this->currentUser->account()->id
            ? $this->accounts->get($accountId)
            : $this->currentUser->account();

        $this->denyAccessUnlessGranted($attribute, $owner);

        return $owner;
    }
}
