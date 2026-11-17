<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Modules\Account\Account;
use App\Modules\Account\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected const string PASSWORD = 'correct horse battery staple';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        // Rate limiter state is persisted in the filesystem cache; isolate tests from each other.
        static::getContainer()->get('cache.rate_limiter')->clear();
    }

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createAccount(string $email = 'user@example.org', bool $admin = false, bool $active = true): Account
    {
        $account = new Account($email, 'Erika', 'Mustermann');
        $account->roles = $admin ? [Role::ADMIN, Role::USER] : [Role::USER];
        $account->active = $active;
        $account->password = static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($account, self::PASSWORD);
        $this->em()->persist($account);
        $this->em()->flush();

        return $account;
    }

    /**
     * @return array{accessToken: string, refreshToken: string}
     */
    protected function login(string $email = 'user@example.org', string $password = self::PASSWORD): array
    {
        $response = $this->request('POST', '/api/v1/auth/login', ['email' => $email, 'password' => $password]);
        self::assertSame(200, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());

        return ['accessToken' => $response['accessToken'], 'refreshToken' => $response['refreshToken']];
    }

    protected function tokenFor(string $email = 'user@example.org', bool $admin = false): string
    {
        $this->createAccount($email, $admin);

        return $this->login($email)['accessToken'];
    }

    /**
     * @param array<mixed>|null $body
     *
     * @return array<mixed>|null
     */
    protected function request(string $method, string $uri, ?array $body = null, ?string $token = null): ?array
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($token !== null) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $this->client->request($method, $uri, server: $server, content: $body !== null ? json_encode($body, \JSON_THROW_ON_ERROR) : null);
        $content = (string) $this->client->getResponse()->getContent();

        return $content === '' ? null : json_decode($content, true, flags: \JSON_THROW_ON_ERROR);
    }

    protected function assertStatus(int $expected): void
    {
        self::assertSame($expected, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());
    }

    /**
     * @param array<mixed>|null $body
     */
    protected function assertProblem(int $status, string $type, ?array $body): void
    {
        $this->assertStatus($status);
        self::assertSame('application/problem+json', $this->client->getResponse()->headers->get('Content-Type'));
        self::assertIsArray($body);
        self::assertSame('urn:workers:problem:' . $type, $body['type'] ?? null);
        self::assertSame($status, $body['status'] ?? null);
        self::assertArrayHasKey('requestId', $body);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function auditRows(string $action, ?string $subjectType = null): array
    {
        $sql = 'SELECT * FROM audit_log WHERE action = ?' . ($subjectType !== null ? ' AND subject_type = ?' : '') . ' ORDER BY seq';
        $params = $subjectType !== null ? [$action, $subjectType] : [$action];

        return $this->em()->getConnection()->fetchAllAssociative($sql, $params);
    }
}
