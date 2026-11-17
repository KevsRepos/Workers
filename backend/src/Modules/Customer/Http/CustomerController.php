<?php

declare(strict_types=1);

namespace App\Modules\Customer\Http;

use App\Modules\Account\Role;
use App\Modules\Customer\CustomerService;
use App\Modules\Customer\Dto\AddressData;
use App\Modules\Customer\Dto\AddressResponse;
use App\Modules\Customer\Dto\CreateCustomerRequest;
use App\Modules\Customer\Dto\CustomerData;
use App\Modules\Customer\Dto\CustomerResponse;
use App\Modules\Customer\Dto\CustomerSummary;
use App\Modules\Customer\Dto\DefaultAddressesRequest;
use App\Shared\Http\PageRequest;
use App\Shared\Http\PageResponse;
use App\Shared\Http\Requirement;
use App\Shared\Http\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/customers', name: 'customers_', requirements: ['id' => Requirement::UUID, 'addressId' => Requirement::UUID])]
#[IsGranted(Role::USER)]
final class CustomerController extends ApiController
{
    public function __construct(private readonly CustomerService $customers)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = PageRequest::fromRequest($request, defaultLimit: 20, maxLimit: 100);
        [$items, $total] = $this->customers->search($request->query->getString('q') ?: null, $page->offset(), $page->limit);

        return $this->json(new PageResponse(array_map(CustomerSummary::fromEntity(...), $items), $page->page, $page->limit, $total));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(CustomerResponse::fromEntity($this->customers->get($id)));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateCustomerRequest $payload): JsonResponse
    {
        $customer = $this->customers->create($payload);

        return $this->persisted(static fn () => CustomerResponse::fromEntity($customer), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] CustomerData $payload): JsonResponse
    {
        $customer = $this->customers->update($this->customers->get($id), $payload);

        return $this->persisted(static fn () => CustomerResponse::fromEntity($customer));
    }

    #[Route('/{id}/addresses', name: 'address_create', methods: ['POST'])]
    public function addAddress(string $id, #[MapRequestPayload] AddressData $payload): JsonResponse
    {
        $address = $this->customers->addAddress($this->customers->get($id), $payload);

        return $this->persisted(static fn () => AddressResponse::fromEntity($address), Response::HTTP_CREATED);
    }

    #[Route('/{id}/addresses/{addressId}', name: 'address_update', methods: ['PUT'])]
    public function updateAddress(string $id, string $addressId, #[MapRequestPayload] AddressData $payload): JsonResponse
    {
        $address = $this->customers->updateAddress($this->customers->get($id), $addressId, $payload);

        return $this->persisted(static fn () => AddressResponse::fromEntity($address));
    }

    #[Route('/{id}/default-addresses', name: 'default_addresses', methods: ['PUT'])]
    public function setDefaultAddresses(string $id, #[MapRequestPayload] DefaultAddressesRequest $payload): JsonResponse
    {
        $customer = $this->customers->setDefaultAddresses($this->customers->get($id), $payload);

        return $this->persisted(static fn () => CustomerResponse::fromEntity($customer));
    }

    #[Route('/{id}/anonymization', name: 'anonymize', methods: ['POST'])]
    #[IsGranted(Role::ADMIN)]
    public function anonymize(string $id): JsonResponse
    {
        $customer = $this->customers->anonymize($this->customers->get($id));

        return $this->persisted(static fn () => CustomerResponse::fromEntity($customer));
    }
}
