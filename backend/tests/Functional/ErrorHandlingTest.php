<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class ErrorHandlingTest extends ApiTestCase
{
    public function testUnknownRouteIsProblemJson(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('GET', '/api/v1/does-not-exist', token: $token);

        $this->assertProblem(404, 'not-found', $body);
    }

    public function testInvalidUuidInPathIsNotFound(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('GET', '/api/v1/customers/not-a-uuid', token: $token);

        $this->assertProblem(404, 'not-found', $body);
    }

    public function testUnknownResourceIsNotFound(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('GET', '/api/v1/customers/01a1eb80-7a0d-7431-b364-cf3fc8bd9acb', token: $token);

        $this->assertProblem(404, 'not-found', $body);
        self::assertStringContainsString('Customer', $body['detail']);
    }

    public function testValidationErrorsListFields(): void
    {
        $token = $this->tokenFor();

        $body = $this->request('POST', '/api/v1/customers', ['company' => false, 'firstName' => '', 'email' => 'not-an-email'], $token);

        $this->assertProblem(422, 'validation-failed', $body);
        $fields = array_column($body['errors'], 'field');
        self::assertContains('email', $fields);
        self::assertContains('firstName', $fields);
        self::assertContains('surname', $fields);
    }

    public function testMalformedJsonIsBadRequest(): void
    {
        $token = $this->tokenFor();
        $this->client->request('POST', '/api/v1/customers', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token], content: '{"broken"');

        $this->assertStatus(400);
        self::assertSame('application/problem+json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testSecurityHeadersAndRequestIdArePresent(): void
    {
        $this->client->request('GET', '/api/v1/health', server: ['HTTP_X_REQUEST_ID' => 'trace-12345678']);

        $headers = $this->client->getResponse()->headers;
        self::assertSame('trace-12345678', $headers->get('X-Request-Id'));
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertStringContainsString('no-store', (string) $headers->get('Cache-Control'));
    }

    public function testHealthEndpointIsPublic(): void
    {
        $body = $this->request('GET', '/api/v1/health');

        $this->assertStatus(200);
        self::assertSame('ok', $body['status']);
    }

    public function testFailedWriteIsRolledBackCompletely(): void
    {
        $token = $this->tokenFor();
        $before = (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM audit_log');

        // Unknown product: the customer lookup succeeds, the use case fails → nothing persisted.
        $customer = $this->request('POST', '/api/v1/customers', ['company' => true, 'companyName' => 'ACME'], $token);
        $afterCustomer = (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM audit_log');

        $this->request('POST', '/api/v1/delivery-notes', [
            'customerId' => $customer['id'],
            'deliveryDate' => '2026-06-01',
            'lines' => [['productId' => '01a1eb80-7a0d-7431-b364-cf3fc8bd9acb', 'quantity' => 1]],
        ], $token);
        $this->assertStatus(422);

        self::assertGreaterThan($before, $afterCustomer);
        self::assertSame($afterCustomer, (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM audit_log'));
        self::assertSame(0, (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM delivery_note'));
        self::assertSame(0, (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM document_sequence'));
    }
}
