<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class ProductTest extends ApiTestCase
{
    public function testAdminManagesProductsAndUsersCanRead(): void
    {
        $adminToken = $this->tokenFor('admin@example.org', admin: true);
        $userToken = $this->tokenFor('user@example.org');

        $deposit = $this->request('POST', '/api/v1/deposits', ['label' => 'Kasten 20x0,5', 'singleAmount' => 8, 'crateAmount' => 150], $adminToken);
        $this->assertStatus(201);

        $product = $this->request('POST', '/api/v1/products', [
            'name' => 'Pils 0,5', 'salesPrice' => 1899, 'sellable' => true, 'rentable' => false, 'quantityInCrate' => 20, 'depositId' => $deposit['id'],
        ], $adminToken);
        $this->assertStatus(201);
        self::assertSame(150, $product['deposit']['crateAmount']);

        $this->request('POST', '/api/v1/products', ['name' => 'Bierzeltgarnitur', 'rentable' => true], $adminToken);
        $this->assertStatus(201);

        $list = $this->request('GET', '/api/v1/products?filter=with-deposit', token: $userToken);
        self::assertSame(1, $list['total']);
        $rentable = $this->request('GET', '/api/v1/products?filter=rentable&q=garnitur', token: $userToken);
        self::assertSame('Bierzeltgarnitur', $rentable['items'][0]['name']);

        $updated = $this->request('PUT', '/api/v1/products/' . $product['id'], ['name' => 'Pils 0,5 l', 'salesPrice' => 1999, 'sellable' => true, 'archived' => true], $adminToken);
        $this->assertStatus(200);
        self::assertTrue($updated['archived']);

        $active = $this->request('GET', '/api/v1/products', token: $userToken);
        self::assertSame(1, $active['total']);
        $archived = $this->request('GET', '/api/v1/products?filter=archived', token: $userToken);
        self::assertSame(1, $archived['total']);
    }

    public function testProductMustBeSellableOrRentable(): void
    {
        $token = $this->tokenFor('admin@example.org', admin: true);

        $body = $this->request('POST', '/api/v1/products', ['name' => 'Nichts'], $token);

        $this->assertProblem(422, 'validation-failed', $body);
    }

    public function testUnknownFilterIsBadRequest(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('GET', '/api/v1/products?filter=nope', token: $token);

        $this->assertProblem(400, 'bad-request', $body);
    }

    public function testProductCanOnlyBelongToOneUnion(): void
    {
        $token = $this->tokenFor('admin@example.org', admin: true);
        $ids = [];
        foreach (['Helles', 'Weizen', 'Radler'] as $name) {
            $ids[] = $this->request('POST', '/api/v1/products', ['name' => $name, 'sellable' => true], $token)['id'];
        }

        $union = $this->request('POST', '/api/v1/product-unions', ['name' => 'Bierkasten', 'productIds' => [$ids[0], $ids[1]]], $token);
        $this->assertStatus(201);
        self::assertCount(2, $union['products']);

        $conflict = $this->request('POST', '/api/v1/product-unions', ['name' => 'Andere', 'productIds' => [$ids[1], $ids[2]]], $token);
        $this->assertProblem(422, 'business-rule-violation', $conflict);

        $updated = $this->request('PUT', '/api/v1/product-unions/' . $union['id'], ['name' => 'Bierkasten', 'productIds' => [$ids[0], $ids[2]]], $token);
        $this->assertStatus(200);
        self::assertSame(['Helles', 'Radler'], array_column($updated['products'], 'name'));

        $this->request('DELETE', '/api/v1/product-unions/' . $union['id'], token: $token);
        $this->assertStatus(204);
    }

    public function testOrderPresetsKeepProducts(): void
    {
        $adminToken = $this->tokenFor('admin@example.org', admin: true);
        $userToken = $this->tokenFor('user@example.org');
        $a = $this->request('POST', '/api/v1/products', ['name' => 'Cola', 'sellable' => true], $adminToken)['id'];
        $b = $this->request('POST', '/api/v1/products', ['name' => 'Wasser', 'sellable' => true], $adminToken)['id'];

        $preset = $this->request('POST', '/api/v1/order-presets', ['name' => 'Kindergeburtstag', 'productIds' => [$a, $b]], $userToken);
        $this->assertStatus(201);
        self::assertCount(2, $preset['products']);

        $updated = $this->request('PUT', '/api/v1/order-presets/' . $preset['id'], ['name' => 'Kindergeburtstag', 'productIds' => [$b]], $userToken);
        self::assertSame(['Wasser'], array_column($updated['products'], 'name'));

        $unknown = $this->request('PUT', '/api/v1/order-presets/' . $preset['id'], ['name' => 'X', 'productIds' => ['01a1eb80-7a0d-7431-b364-cf3fc8bd9acb']], $userToken);
        $this->assertProblem(422, 'business-rule-violation', $unknown);

        $this->request('DELETE', '/api/v1/order-presets/' . $preset['id'], token: $userToken);
        $this->assertStatus(204);
    }
}
