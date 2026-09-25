<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use Error;
use Exception;
use App\Modules\Customer\Address\Dto\CreateCustomerAddressRequestDto;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Modules\Customer\Address\Dto\AddressResponseDto;
use App\Modules\Customer\Service as CustomerService;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private CustomerService $customerService,
    ) {}

    public function save(string $customerId, CreateCustomerAddressRequestDto $data): Error|Success
    {
        $address = $this->factory->create(
            $data->street,
            $data->houseNumber,
            $data->postalCode,
            $data->city,
            $data->country,
            $customerId
        );

        try {
            $this->repo->save($address, true);

            if ($data->standardShippingAddress) {
                $this->customerService->setDefaultShippingAddress($customerId, $address->id);
            }
            if ($data->standardBillingAddress) {
                $this->customerService->setDefaultBillingAddress($customerId, $address->id);
            }
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("CustomerAddressCreated", [new AddressResponseDto(
            $address->id,
            $address->street,
            $address->houseNumber,
            $address->postalCode,
            $address->city,
            $address->country,
            $address->defaultShippingAddress,
            $address->defaultBillingAddress
        )]);
    }

    public function listAddresses(): array
    {
        return $this->repo->findAll();
    }

    public function listAddressesByCustomerId(string $customerId): array
    {
        $addresses = $this->repo->findByCustomerId($customerId);

        $responseAddresses = array_map(
            fn($address) => new AddressResponseDto(
                $address->id,
                $address->street,
                $address->houseNumber,
                $address->city,
                $address->postalCode,
                $address->country,
                $address->defaultShippingAddress,
                $address->defaultBillingAddress
            ),
            $addresses
        );

        return $responseAddresses;
    }

    public function getAddress(string $id): ?CustomerAddress
    {
        return $this->repo->find($id);
    }
}
