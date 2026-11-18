<?php

declare(strict_types=1);

namespace App\Modules\Customer;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use App\Modules\Customer\Address\CustomerAddress;
use App\Modules\Customer\Dto\AddressData;
use App\Modules\Customer\Dto\CreateCustomerRequest;
use App\Modules\Customer\Dto\CustomerData;
use App\Modules\Customer\Dto\DefaultAddressesRequest;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\ConflictException;
use App\Shared\Exception\NotFoundException;
use Psr\Clock\ClockInterface;

final readonly class CustomerService
{
    public function __construct(
        private Repository $customers,
        private AuditTrail $auditTrail,
        private ClockInterface $clock,
    ) {
    }

    public function get(string $id): Customer
    {
        return $this->customers->findWithAddresses($id) ?? throw NotFoundException::resource('Customer', $id);
    }

    /**
     * @return array{list<Customer>, int}
     */
    public function search(?string $query, int $offset, int $limit): array
    {
        return $this->customers->search($query !== null ? trim($query) : null, $offset, $limit);
    }

    public function create(CreateCustomerRequest $request): Customer
    {
        $customer = new Customer();
        $this->apply($customer, $request);

        foreach ($request->addresses as $data) {
            $address = $this->newAddress($customer, $data);
            if ($data->defaultShipping || ($customer->defaultShippingAddress === null && \count($request->addresses) === 1)) {
                $customer->defaultShippingAddress = $address;
            }
            if ($data->defaultBilling || ($customer->defaultBillingAddress === null && \count($request->addresses) === 1)) {
                $customer->defaultBillingAddress = $address;
            }
        }

        $this->customers->add($customer);

        return $customer;
    }

    public function update(Customer $customer, CustomerData $data): Customer
    {
        $this->assertNotAnonymized($customer);
        $this->apply($customer, $data);

        return $customer;
    }

    public function addAddress(Customer $customer, AddressData $data): CustomerAddress
    {
        $this->assertNotAnonymized($customer);
        $address = $this->newAddress($customer, $data);

        if ($data->defaultShipping || $customer->defaultShippingAddress === null) {
            $customer->defaultShippingAddress = $address;
        }
        if ($data->defaultBilling || $customer->defaultBillingAddress === null) {
            $customer->defaultBillingAddress = $address;
        }

        return $address;
    }

    public function updateAddress(Customer $customer, string $addressId, AddressData $data): CustomerAddress
    {
        $this->assertNotAnonymized($customer);
        $address = $this->ownedAddress($customer, $addressId, 'addressId');
        $this->fillAddress($address, $data);

        if ($data->defaultShipping) {
            $customer->defaultShippingAddress = $address;
        }
        if ($data->defaultBilling) {
            $customer->defaultBillingAddress = $address;
        }

        return $address;
    }

    public function setDefaultAddresses(Customer $customer, DefaultAddressesRequest $request): Customer
    {
        $customer->defaultShippingAddress = $request->shippingAddressId !== null ? $this->ownedAddress($customer, $request->shippingAddressId, 'shippingAddressId') : null;
        $customer->defaultBillingAddress = $request->billingAddressId !== null ? $this->ownedAddress($customer, $request->billingAddressId, 'billingAddressId') : null;

        return $customer;
    }

    /**
     * Resolves an address and guarantees that it belongs to the given customer.
     */
    public function ownedAddress(Customer $customer, string $addressId, string $field): CustomerAddress
    {
        $address = $this->customers->findAddress($addressId);
        if ($address === null || !$customer->ownsAddress($address)) {
            throw BusinessRuleException::forField($field, 'The address does not belong to this customer.', 'address-not-owned');
        }

        return $address;
    }

    /**
     * GDPR Art. 17: erase personal data. Customers referenced by delivery notes are subject to
     * statutory retention (HGB § 257, AO § 147) and cannot be anonymised before it ends.
     */
    public function anonymize(Customer $customer): Customer
    {
        $this->assertNotAnonymized($customer);
        if ($this->customers->hasDeliveryNotes($customer)) {
            throw new ConflictException('The customer is referenced by delivery notes that are subject to statutory retention and cannot be anonymised yet.');
        }

        $customer->firstName = null;
        $customer->surname = null;
        $customer->companyName = $customer->company ? 'Anonymisiert' : null;
        if (!$customer->company) {
            $customer->firstName = 'Anonymisiert';
            $customer->surname = '';
        }
        $customer->email = null;
        $customer->phone = null;
        foreach ($customer->addresses as $address) {
            $address->street = '—';
            $address->houseNumber = '—';
            $address->postalCode = '—';
            $address->city = '—';
            $address->country = null;
            $address->note = null;
        }
        $customer->anonymizedAt = $this->clock->now();

        $this->auditTrail->record(AuditAction::DATA_ANONYMIZED, 'Customer', $customer->id);

        return $customer;
    }

    private function apply(Customer $customer, CustomerData $data): void
    {
        $customer->company = $data->company;
        $customer->firstName = self::clean($data->firstName);
        $customer->surname = self::clean($data->surname);
        $customer->companyName = $data->company ? self::clean($data->companyName) : null;
        $customer->email = self::clean($data->email !== null ? mb_strtolower($data->email) : null);
        $customer->phone = self::clean($data->phone);
    }

    private function newAddress(Customer $customer, AddressData $data): CustomerAddress
    {
        $address = new CustomerAddress();
        $this->fillAddress($address, $data);
        $customer->addAddress($address);

        return $address;
    }

    private function fillAddress(CustomerAddress $address, AddressData $data): void
    {
        $address->street = trim($data->street);
        $address->houseNumber = trim($data->houseNumber);
        $address->postalCode = trim($data->postalCode);
        $address->city = trim($data->city);
        $address->country = self::clean($data->country);
        $address->note = self::clean($data->note);
    }

    private function assertNotAnonymized(Customer $customer): void
    {
        if ($customer->anonymizedAt !== null) {
            throw new ConflictException('The customer has been anonymised and can no longer be changed.');
        }
    }

    private static function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}
