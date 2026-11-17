<?php

declare(strict_types=1);

namespace App\Modules\Account\Http;

use App\Modules\Account\AccountService;
use App\Modules\Account\Dto\AccountResponse;
use App\Modules\Account\Dto\CreateAccountRequest;
use App\Modules\Account\Dto\ResetPasswordRequest;
use App\Modules\Account\Dto\UpdateAccountRequest;
use App\Modules\Account\Role;
use App\Shared\Http\Requirement;
use App\Shared\Security\CurrentUser;
use App\Shared\Http\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/accounts', name: 'admin_accounts_')]
#[IsGranted(Role::ADMIN)]
final class AdminAccountController extends ApiController
{
    public function __construct(private readonly AccountService $accounts)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_map(AccountResponse::fromEntity(...), $this->accounts->list()));
    }

    #[Route('/{id}', name: 'get', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(AccountResponse::fromEntity($this->accounts->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateAccountRequest $payload): JsonResponse
    {
        $account = $this->accounts->create($payload);

        return $this->persisted(static fn () => AccountResponse::fromEntity($account), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => Requirement::UUID], methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] UpdateAccountRequest $payload, CurrentUser $currentUser): JsonResponse
    {
        $account = $this->accounts->update($this->accounts->get($id), $payload, $currentUser->account());

        return $this->persisted(static fn () => AccountResponse::fromEntity($account));
    }

    #[Route('/{id}/password', name: 'reset_password', requirements: ['id' => Requirement::UUID], methods: ['PUT'])]
    public function resetPassword(string $id, #[MapRequestPayload] ResetPasswordRequest $payload): Response
    {
        $this->accounts->resetPassword($this->accounts->get($id), $payload->password);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
