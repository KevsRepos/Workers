<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use Error;
use Exception;
use App\Modules\Pim\Deposit\Dto\CreateDepositRequestDto;
use App\Modules\Pim\Deposit\Repository;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Uid\Uuid;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory
    ) {}

    public function save(CreateDepositRequestDto $data): Error|Success
    {
        $deposit = $this->factory->create(
            $data->singleAmount,
            $data->crateAmount
        );

        try {
            $this->repo->save($deposit, true);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception $e) {
            return new Error("Error", 500);
        }

        return new Success("DepositCreated");
    }

    public function list(): array
    {
        return $this->repo->listAll();
    }
}
