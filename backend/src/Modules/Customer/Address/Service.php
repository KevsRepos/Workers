<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use Error;
use Exception;
use App\Modules\Customer\Address\Dto\CreateCustomerAddressRequestDto;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory
    ) {}

    public function save(CreateCustomerAddressRequestDto $data): Error|Success
    {
        $address = $this->factory->create(
            $data->street,
            $data->houseNumber,
            $data->postalCode,
            $data->city,
            $data->country,
            $data->isPrimary ?? false,
            $data->customerId
        );

        try {
            $this->repo->save($address, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("CustomerAddressCreated");
    }

    public function listAddresses(): array
    {
        return $this->repo->findAll();
    }

    public function getAddress(string $id): ?CustomerAddress
    {
        return $this->repo->find($id);
    }
}
