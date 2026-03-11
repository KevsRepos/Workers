<?php declare(strict_types=1);

namespace App\Modules\Customer;

use Error;
use Exception;
use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory
    ) {}

    public function save(CreateCustomerRequestDto $data): Error|Success
    {
        $customer = $this->factory->create(
            $data->firstName,
            $data->surname
        );

        try {
            $customer = $this->repo->save($customer, true);

            return new Success("CustomerCreated", ['customer' => $customer]);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }
    }

    public function search(string $query): array
    {
        return $this->repo->search($query);
    }

    public function findById(string $id): ?Customer
    {
        return $this->repo->findById($id);
    }
}
