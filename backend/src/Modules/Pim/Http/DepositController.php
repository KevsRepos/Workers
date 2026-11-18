<?php

declare(strict_types=1);

namespace App\Modules\Pim\Http;

use App\Modules\Account\Role;
use App\Modules\Pim\Deposit\Deposit;
use App\Modules\Pim\Deposit\Dto\DepositData;
use App\Modules\Pim\Deposit\Dto\DepositResponse;
use App\Modules\Pim\Deposit\Repository;
use App\Shared\Exception\NotFoundException;
use App\Shared\Http\ApiController;
use App\Shared\Http\Requirement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/deposits', name: 'deposits_', requirements: ['id' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class DepositController extends ApiController
{
    public function __construct(private readonly Repository $deposits)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_map(DepositResponse::fromEntity(...), $this->deposits->findAllOrdered()));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(Role::ADMIN)]
    public function create(#[MapRequestPayload] DepositData $payload): JsonResponse
    {
        $deposit = new Deposit();
        self::apply($deposit, $payload);
        $this->deposits->add($deposit);

        return $this->persisted(static fn () => DepositResponse::fromEntity($deposit), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted(Role::ADMIN)]
    public function update(string $id, #[MapRequestPayload] DepositData $payload): JsonResponse
    {
        $deposit = $this->deposits->find($id) ?? throw NotFoundException::resource('Deposit', $id);
        self::apply($deposit, $payload);

        return $this->persisted(static fn () => DepositResponse::fromEntity($deposit));
    }

    private static function apply(Deposit $deposit, DepositData $data): void
    {
        $deposit->label = $data->label !== null && trim($data->label) !== '' ? trim($data->label) : null;
        $deposit->singleAmount = $data->singleAmount;
        $deposit->crateAmount = $data->crateAmount;
    }
}
