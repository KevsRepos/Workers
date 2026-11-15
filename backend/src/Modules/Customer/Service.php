<?php declare(strict_types=1);

namespace App\Modules\Customer;

use Error;
use App\Modules\Customer\Address\Dto\AddressResponseDto;
use App\Modules\Customer\Dto\CustomerResponseDto;
use Exception;
use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use App\Lib\Success;
use App\Modules\Customer\Address\CustomerAddress;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory,
    ) {}

    public function save(CreateCustomerRequestDto $data): Error|Success
    {
        $customer = $this->factory->create($data);

        try {
            $customer = $this->repo->save($customer, true);

            $responseAddresses = array_map(
                fn($address) => new AddressResponseDto(
                    $address->id,
                    $address->street,
                    $address->houseNumber,
                    $address->postalCode,
                    $address->city,
                    $address->country,
                    $address->id === $customer->defaultShippingAddress?->id,
                    $address->id === $customer->defaultBillingAddress?->id
                ),
                $customer->addresses->toArray()
            );

            $defaultShippingAddress = $customer->defaultShippingAddress ? new AddressResponseDto(
                $customer->defaultShippingAddress->id,
                $customer->defaultShippingAddress->street,
                $customer->defaultShippingAddress->houseNumber,
                $customer->defaultShippingAddress->postalCode,
                $customer->defaultShippingAddress->city,
                $customer->defaultShippingAddress->country,
                true,
                false
            ) : null;

            $defaultBillingAddress = $customer->defaultBillingAddress ? new AddressResponseDto(
                $customer->defaultBillingAddress->id,
                $customer->defaultBillingAddress->street,
                $customer->defaultBillingAddress->houseNumber,
                $customer->defaultBillingAddress->postalCode,
                $customer->defaultBillingAddress->city,
                $customer->defaultBillingAddress->country,
                false,
                true
            ) : null;

            $customerResponse = new CustomerResponseDto(
                $customer->id,
                $customer->firstName,
                $customer->surname,
                $customer->company,
                $customer->companyName,
                $customer->displayName,
                $customer->email,
                $customer->phone,
                $defaultShippingAddress,
                $defaultBillingAddress,
                $responseAddresses
            );

            return new Success("CustomerCreated", ['customer' => $customerResponse]);
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

    public function createCustomerResponse(Customer $customer): CustomerResponseDto
    {
        $responseAddresses = array_map(
            fn($address) => new AddressResponseDto(
                $address->id,
                $address->street,
                $address->houseNumber,
                $address->postalCode,
                $address->city,
                $address->country,
                $address->id === $customer->defaultShippingAddress?->id,
                $address->id === $customer->defaultBillingAddress?->id
            ),
            $customer->addresses->toArray()
        );

        $defaultShippingAddress = $customer->defaultShippingAddress ? new AddressResponseDto(
            $customer->defaultShippingAddress->id,
            $customer->defaultShippingAddress->street,
            $customer->defaultShippingAddress->houseNumber,
            $customer->defaultShippingAddress->postalCode,
            $customer->defaultShippingAddress->city,
            $customer->defaultShippingAddress->country,
            true,
            false
        ) : null;

        $defaultBillingAddress = $customer->defaultBillingAddress ? new AddressResponseDto(
            $customer->defaultBillingAddress->id,
            $customer->defaultBillingAddress->street,
            $customer->defaultBillingAddress->houseNumber,
            $customer->defaultBillingAddress->postalCode,
            $customer->defaultBillingAddress->city,
            $customer->defaultBillingAddress->country,
            false,
            true
        ) : null;

        $customerResponse = new CustomerResponseDto(
            $customer->id,
            $customer->firstName,
            $customer->surname,
            $customer->company,
            $customer->companyName,
            $customer->displayName,
            $customer->email,
            $customer->phone,
            $defaultShippingAddress,
            $defaultBillingAddress,
            $responseAddresses
        );

        return $customerResponse;
    }

    public function setDefaultShippingAddress(CustomerAddress $address): Error|Success
    {
        try {
            $this->repo->saveDefaultShippingAddress($address->customer->id, $address->id);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }
        return new Success("DefaultShippingAddressSet");
    }

    public function setDefaultBillingAddress(CustomerAddress $address): Error|Success
    {
        try {
            $this->repo->saveDefaultBillingAddress($address->customer->id, $address->id);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }
        return new Success("DefaultBillingAddressSet");
    }
}
