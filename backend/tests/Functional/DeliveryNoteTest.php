<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Audit\AuditAction;

final class DeliveryNoteTest extends ApiTestCase
{
    private string $token;
    /** @var array<string, mixed> */
    private array $customer;
    /** @var array<string, string> */
    private array $products = [];

    protected function setUp(): void
    {
        parent::setUp();
        $adminToken = $this->tokenFor('admin@example.org', admin: true);
        $this->token = $this->tokenFor('user@example.org');

        $deposit = $this->request('POST', '/api/v1/deposits', ['singleAmount' => 8, 'crateAmount' => 150], $adminToken);
        foreach (['Helles' => true, 'Weizen' => true, 'Stehtisch' => false] as $name => $withDeposit) {
            $this->products[$name] = $this->request('POST', '/api/v1/products', [
                'name' => $name, 'sellable' => $withDeposit, 'rentable' => !$withDeposit, 'quantityInCrate' => $withDeposit ? 20 : null, 'depositId' => $withDeposit ? $deposit['id'] : null,
            ], $adminToken)['id'];
        }
        $this->request('POST', '/api/v1/product-unions', ['name' => 'Bierkasten', 'productIds' => [$this->products['Helles'], $this->products['Weizen']]], $adminToken);

        $this->customer = $this->request('POST', '/api/v1/customers', [
            'company' => true,
            'companyName' => 'Sportverein e. V.',
            'addresses' => [['street' => 'Am Platz', 'houseNumber' => '1', 'postalCode' => '12345', 'city' => 'Musterstadt']],
        ], $this->token);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'customerId' => $this->customer['id'],
            'deliveryDate' => '2026-07-10',
            'pickupDate' => '2026-07-12',
            'delivery' => true,
            'shippingAddressId' => $this->customer['addresses'][0]['id'],
            'billingAddressId' => $this->customer['addresses'][0]['id'],
            'shortDescription' => 'Sommerfest',
            'privateDescription' => 'Schlüssel beim Hausmeister',
            'assignment' => 'Tom',
            'adultGuests' => 120,
            'callBefore' => true,
            'lines' => [
                ['productId' => $this->products['Helles'], 'quantity' => 10],
                ['productId' => $this->products['Weizen'], 'quantity' => 5],
                ['productId' => $this->products['Stehtisch'], 'quantity' => 8],
            ],
        ];
    }

    public function testCreateAssignsSequentialNumbersAndSnapshot(): void
    {
        $first = $this->request('POST', '/api/v1/delivery-notes', $this->payload(), $this->token);
        $this->assertStatus(201);
        $second = $this->request('POST', '/api/v1/delivery-notes', $this->payload(), $this->token);

        self::assertSame('LS-2026-00001', $first['number']);
        self::assertSame('LS-2026-00002', $second['number']);
        self::assertSame(1, $first['status']);
        self::assertSame('Sportverein e. V.', $first['customer']['displayName']);
        self::assertCount(3, $first['lines']);
        self::assertNotNull($first['lines'][0]['id']);
        self::assertSame('Helles', $first['lines'][0]['productName']);
        self::assertSame('Erika Mustermann', $first['createdBy']);

        $list = $this->request('GET', '/api/v1/delivery-notes?status=1', token: $this->token);
        self::assertSame(2, $list['total']);
        $search = $this->request('GET', '/api/v1/delivery-notes?q=sportverein', token: $this->token);
        self::assertSame(2, $search['total']);

        self::assertCount(2, $this->auditRows(AuditAction::ENTITY_CREATED, 'DeliveryNote'));
        self::assertCount(6, $this->auditRows(AuditAction::ENTITY_CREATED, 'DeliveryNoteProduct'));
    }

    public function testRejectsForeignAddressAndInvalidDates(): void
    {
        $other = $this->request('POST', '/api/v1/customers', ['company' => true, 'companyName' => 'Fremd', 'addresses' => [['street' => 'X', 'houseNumber' => '1', 'postalCode' => '1', 'city' => 'Y']]], $this->token);

        $foreign = $this->request('POST', '/api/v1/delivery-notes', $this->payload(['shippingAddressId' => $other['addresses'][0]['id']]), $this->token);
        $this->assertProblem(422, 'business-rule-violation', $foreign);

        $dates = $this->request('POST', '/api/v1/delivery-notes', $this->payload(['pickupDate' => '2026-07-01']), $this->token);
        $this->assertProblem(422, 'validation-failed', $dates);

        $noLines = $this->request('POST', '/api/v1/delivery-notes', $this->payload(['lines' => []]), $this->token);
        $this->assertProblem(422, 'validation-failed', $noLines);

        $noAddress = $this->request('POST', '/api/v1/delivery-notes', $this->payload(['shippingAddressId' => null]), $this->token);
        $this->assertProblem(422, 'business-rule-violation', $noAddress);
    }

    public function testUpdateTreatsLinesAsAuthoritative(): void
    {
        $note = $this->request('POST', '/api/v1/delivery-notes', $this->payload(), $this->token);
        [$helles, , $tisch] = $note['lines'];

        $updated = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'], $this->payload([
            'lines' => [
                ['id' => $tisch['id'], 'productId' => $this->products['Stehtisch'], 'quantity' => 10],
                ['id' => $helles['id'], 'productId' => $this->products['Helles'], 'quantity' => 12],
            ],
        ]), $this->token);

        $this->assertStatus(200);
        self::assertSame(['Stehtisch', 'Helles'], array_column($updated['lines'], 'productName'));
        self::assertEquals([10, 12], array_column($updated['lines'], 'quantity'));
        self::assertSame($note['number'], $updated['number']);
        self::assertCount(1, $this->auditRows(AuditAction::ENTITY_DELETED, 'DeliveryNoteProduct'));
    }

    public function testStatusQuantitiesAndReturnNote(): void
    {
        $note = $this->request('POST', '/api/v1/delivery-notes', $this->payload(), $this->token);

        $delivered = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/status', ['status' => 2], $this->token);
        self::assertSame('Geliefert', $delivered['statusLabel']);

        $invalid = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/status', ['status' => 99], $this->token);
        $this->assertProblem(422, 'validation-failed', $invalid);

        $quantities = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/quantities', ['lines' => [['id' => $note['lines'][2]['id'], 'quantity' => 6]]], $this->token);
        self::assertEquals(6, $quantities['lines'][2]['quantity']);

        $groups = $this->request('GET', '/api/v1/delivery-notes/' . $note['id'] . '/return-groups', token: $this->token);
        self::assertCount(2, $groups['groups']);
        $crate = $groups['groups'][0];
        self::assertTrue($crate['isUnion']);
        self::assertSame('Bierkasten', $crate['name']);
        self::assertEquals(15, $crate['quantity']);

        $returned = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/return-note', ['entries' => [
            ['lineIds' => $crate['lineIds'], 'returnedTotal' => 14, 'returnedFull' => 3, 'returnedTotalBottles' => 5, 'returnedFullBottles' => 0],
            ['lineIds' => [$note['lines'][2]['id']], 'returnedTotal' => 6],
        ]], $this->token);
        $this->assertStatus(200);
        self::assertSame(4, $returned['status']);
        self::assertSame(14, $returned['lines'][0]['returnEntry']['returnedTotal']);
        self::assertSame($returned['lines'][0]['returnEntry']['id'], $returned['lines'][1]['returnEntry']['id']);

        // Correcting the return note updates the entry instead of creating duplicates.
        $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/return-note', ['entries' => [
            ['lineIds' => $crate['lineIds'], 'returnedTotal' => 15, 'returnedFull' => 3],
        ]], $this->token);
        self::assertSame(2, (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM return_note_entry'));

        // Lines with recorded returns cannot be removed.
        $conflict = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'], $this->payload(['lines' => [['id' => $note['lines'][2]['id'], 'productId' => $this->products['Stehtisch'], 'quantity' => 6]]]), $this->token);
        $this->assertProblem(409, 'conflict', $conflict);

        $tooMany = $this->request('PUT', '/api/v1/delivery-notes/' . $note['id'] . '/return-note', ['entries' => [['lineIds' => [$note['lines'][2]['id']], 'returnedTotal' => 1, 'returnedFull' => 2]]], $this->token);
        $this->assertProblem(422, 'validation-failed', $tooMany);
    }

    public function testListValidatesParameters(): void
    {
        $this->request('GET', '/api/v1/delivery-notes?status=42', token: $this->token);
        $this->assertStatus(400);
        $this->request('GET', '/api/v1/delivery-notes?from=yesterday', token: $this->token);
        $this->assertStatus(400);

        $counts = $this->request('GET', '/api/v1/delivery-notes/status-counts', token: $this->token);
        $this->assertStatus(200);
        self::assertSame(0, $counts['1']);
    }
}
