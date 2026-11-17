<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;

final class AuthorizationTest extends ApiTestCase
{
    /**
     * @return iterable<string, array{string, string, array<mixed>|null}>
     */
    public static function adminOnlyEndpoints(): iterable
    {
        yield 'list accounts' => ['GET', '/api/v1/admin/accounts', null];
        yield 'create account' => ['POST', '/api/v1/admin/accounts', ['emailAddress' => 'x@example.org', 'firstName' => 'X', 'surname' => 'Y', 'password' => 'correct horse battery staple']];
        yield 'audit log' => ['GET', '/api/v1/admin/audit-log', null];
        yield 'audit verification' => ['GET', '/api/v1/admin/audit-log/verification', null];
        yield 'create product' => ['POST', '/api/v1/products', ['name' => 'Bier', 'sellable' => true]];
        yield 'create deposit' => ['POST', '/api/v1/deposits', ['singleAmount' => 8]];
        yield 'create product union' => ['POST', '/api/v1/product-unions', ['name' => 'Kasten', 'productIds' => []]];
    }

    /**
     * @param array<mixed>|null $body
     */
    #[DataProvider('adminOnlyEndpoints')]
    public function testRegularUserIsForbidden(string $method, string $uri, ?array $body): void
    {
        $token = $this->tokenFor('user@example.org');

        $response = $this->request($method, $uri, $body, $token);

        $this->assertProblem(403, 'forbidden', $response);
    }

    public function testAdminCanManageAccounts(): void
    {
        $token = $this->tokenFor('admin@example.org', admin: true);

        $created = $this->request('POST', '/api/v1/admin/accounts', [
            'emailAddress' => 'New.Person@Example.org',
            'firstName' => 'New',
            'surname' => 'Person',
            'password' => 'correct horse battery staple',
            'roles' => ['ROLE_USER'],
        ], $token);
        $this->assertStatus(201);
        self::assertSame('new.person@example.org', $created['emailAddress']);

        $duplicate = $this->request('POST', '/api/v1/admin/accounts', [
            'emailAddress' => 'new.person@example.org', 'firstName' => 'A', 'surname' => 'B', 'password' => 'correct horse battery staple',
        ], $token);
        $this->assertProblem(409, 'conflict', $duplicate);

        $updated = $this->request('PUT', '/api/v1/admin/accounts/' . $created['id'], [
            'emailAddress' => 'new.person@example.org', 'firstName' => 'New', 'surname' => 'Person', 'roles' => ['ROLE_USER'], 'active' => false,
        ], $token);
        $this->assertStatus(200);
        self::assertFalse($updated['active']);

        $this->request('POST', '/api/v1/auth/login', ['email' => 'new.person@example.org', 'password' => 'correct horse battery staple']);
        $this->assertStatus(401);
    }

    public function testAdminCannotLockThemselvesOut(): void
    {
        $admin = $this->createAccount('admin@example.org', admin: true);
        $token = $this->login('admin@example.org')['accessToken'];

        $body = $this->request('PUT', '/api/v1/admin/accounts/' . $admin->id, [
            'emailAddress' => 'admin@example.org', 'firstName' => 'A', 'surname' => 'B', 'roles' => ['ROLE_USER'], 'active' => true,
        ], $token);

        $this->assertProblem(422, 'business-rule-violation', $body);
    }

    public function testAdminPasswordResetEndsSessions(): void
    {
        $user = $this->createAccount('user@example.org');
        $userTokens = $this->login('user@example.org');
        $adminToken = $this->tokenFor('admin@example.org', admin: true);

        $this->request('PUT', '/api/v1/admin/accounts/' . $user->id . '/password', ['password' => 'another strong passphrase 99'], $adminToken);
        $this->assertStatus(204);

        $this->request('POST', '/api/v1/auth/refresh', ['refreshToken' => $userTokens['refreshToken']]);
        $this->assertStatus(401);
    }
}
