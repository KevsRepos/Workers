<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Audit\AuditAction;

final class CustomerTest extends ApiTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function personPayload(): array
    {
        return [
            'company' => false,
            'firstName' => ' Max ',
            'surname' => 'Mustermann',
            'email' => 'Max@Example.org',
            'phone' => '+49 170 1234567',
            'addresses' => [
                ['street' => 'Hauptstraße', 'houseNumber' => '1', 'postalCode' => '12345', 'city' => 'Musterstadt'],
                ['street' => 'Nebenweg', 'houseNumber' => '2a', 'postalCode' => '54321', 'city' => 'Anderswo', 'defaultBilling' => true],
            ],
        ];
    }

    public function testCreateSearchAndGetCustomer(): void
    {
        $token = $this->tokenFor();

        $created = $this->request('POST', '/api/v1/customers', $this->personPayload(), $token);
        $this->assertStatus(201);
        self::assertSame('Max Mustermann', $created['displayName']);
        self::assertSame('max@example.org', $created['email']);
        self::assertCount(2, $created['addresses']);
        self::assertNotNull($created['addresses'][0]['id']);
        self::assertSame($created['addresses'][1]['id'], $created['defaultBillingAddressId']);

        $search = $this->request('GET', '/api/v1/customers?q=muster%20max', token: $token);
        $this->assertStatus(200);
        self::assertSame(1, $search['total']);
        self::assertSame($created['id'], $search['items'][0]['id']);

        $none = $this->request('GET', '/api/v1/customers?q=nobody', token: $token);
        self::assertSame(0, $none['total']);

        $fetched = $this->request('GET', '/api/v1/customers/' . $created['id'], token: $token);
        self::assertSame($created['id'], $fetched['id']);

        $audit = $this->auditRows(AuditAction::ENTITY_CREATED, 'Customer');
        self::assertCount(1, $audit);
        self::assertSame('user@example.org', $audit[0]['actor_label']);
    }

    public function testCompanyRequiresCompanyName(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('POST', '/api/v1/customers', ['company' => true, 'companyName' => ' '], $token);

        $this->assertProblem(422, 'validation-failed', $body);
        self::assertContains('companyName', array_column($body['errors'], 'field'));
    }

    public function testUpdateCustomerAndAddresses(): void
    {
        $token = $this->tokenFor();
        $customer = $this->request('POST', '/api/v1/customers', $this->personPayload(), $token);

        $updated = $this->request('PUT', '/api/v1/customers/' . $customer['id'], ['company' => true, 'companyName' => 'Muster GmbH', 'firstName' => 'Max', 'surname' => 'Mustermann'], $token);
        $this->assertStatus(200);
        self::assertSame('Muster GmbH', $updated['displayName']);

        $address = $this->request('POST', '/api/v1/customers/' . $customer['id'] . '/addresses', ['street' => 'Neu', 'houseNumber' => '3', 'postalCode' => '11111', 'city' => 'Neustadt', 'defaultShipping' => true], $token);
        $this->assertStatus(201);
        self::assertTrue($address['defaultShipping']);

        $edited = $this->request('PUT', '/api/v1/customers/' . $customer['id'] . '/addresses/' . $address['id'], ['street' => 'Neu', 'houseNumber' => '4', 'postalCode' => '11111', 'city' => 'Neustadt'], $token);
        $this->assertStatus(200);
        self::assertSame('4', $edited['houseNumber']);

        $audit = $this->auditRows(AuditAction::ENTITY_UPDATED, 'CustomerAddress');
        self::assertCount(1, $audit);
        self::assertEquals(['houseNumber' => ['old' => '3', 'new' => '4']], json_decode((string) $audit[0]['changes'], true));
    }

    public function testAddressOfAnotherCustomerCannotBeUsed(): void
    {
        $token = $this->tokenFor();
        $first = $this->request('POST', '/api/v1/customers', $this->personPayload(), $token);
        $second = $this->request('POST', '/api/v1/customers', ['company' => true, 'companyName' => 'Other'], $token);

        $body = $this->request('PUT', '/api/v1/customers/' . $second['id'] . '/default-addresses', ['shippingAddressId' => $first['addresses'][0]['id']], $token);

        $this->assertProblem(422, 'business-rule-violation', $body);
    }

    public function testAnonymizationRequiresAdminAndRespectsRetention(): void
    {
        $userToken = $this->tokenFor();
        $adminToken = $this->tokenFor('admin@example.org', admin: true);
        $customer = $this->request('POST', '/api/v1/customers', $this->personPayload(), $userToken);

        $this->request('POST', '/api/v1/customers/' . $customer['id'] . '/anonymization', token: $userToken);
        $this->assertStatus(403);

        $anonymized = $this->request('POST', '/api/v1/customers/' . $customer['id'] . '/anonymization', token: $adminToken);
        $this->assertStatus(200);
        self::assertTrue($anonymized['anonymized']);
        self::assertNull($anonymized['email']);
        self::assertSame('—', $anonymized['addresses'][0]['street']);
        self::assertCount(1, $this->auditRows(AuditAction::DATA_ANONYMIZED));

        $search = $this->request('GET', '/api/v1/customers?q=Muster', token: $userToken);
        self::assertSame(0, $search['total']);

        $this->request('PUT', '/api/v1/customers/' . $customer['id'], ['company' => true, 'companyName' => 'Again'], $userToken);
        $this->assertStatus(409);
    }
}
