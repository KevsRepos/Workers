<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Audit\AuditHasher;
use App\Audit\AuditLogReader;
use Doctrine\DBAL\Exception\DriverException;

final class AuditLogTest extends ApiTestCase
{
    public function testAdminCanSearchAndVerifyAuditLog(): void
    {
        $token = $this->tokenFor('admin@example.org', admin: true);
        $customer = $this->request('POST', '/api/v1/customers', ['company' => true, 'companyName' => 'ACME'], $token);

        $page = $this->request('GET', '/api/v1/admin/audit-log?subjectType=Customer&subjectId=' . $customer['id'], token: $token);
        $this->assertStatus(200);
        self::assertSame(1, $page['total']);
        $entry = $page['items'][0];
        self::assertSame('entity.created', $entry['action']);
        self::assertSame('admin@example.org', $entry['actorLabel']);
        self::assertSame('ACME', $entry['changes']['companyName']['new']);
        self::assertSame('http', $entry['context']['channel']);
        self::assertArrayHasKey('requestId', $entry['context']);

        $verification = $this->request('GET', '/api/v1/admin/audit-log/verification', token: $token);
        self::assertTrue($verification['valid']);
        self::assertGreaterThanOrEqual(3, $verification['checked']);
    }

    public function testDatabaseRejectsUpdatesAndDeletes(): void
    {
        $this->tokenFor('admin@example.org', admin: true);
        $connection = $this->em()->getConnection();

        foreach (['UPDATE audit_log SET action = ? WHERE seq = 1' => ['tampered'], 'DELETE FROM audit_log WHERE seq = 1' => []] as $sql => $params) {
            try {
                $connection->executeStatement($sql, $params);
                self::fail('Statement should have been rejected: ' . $sql);
            } catch (DriverException $e) {
                self::assertStringContainsString('append-only', $e->getMessage());
            }
        }
    }

    public function testVerificationDetectsForgedEntries(): void
    {
        $this->tokenFor('admin@example.org', admin: true);
        $connection = $this->em()->getConnection();
        $reader = static::getContainer()->get(AuditLogReader::class);
        self::assertTrue($reader->verify()['valid']);

        // An attacker with INSERT rights appends a row without knowing the chain.
        $head = $connection->fetchAssociative('SELECT last_seq, last_hash FROM audit_chain_head');
        $connection->insert('audit_log', [
            'seq' => (int) $head['last_seq'] + 1,
            'event_id' => '00000000-0000-4000-8000-000000000001',
            'occurred_at' => '2026-01-01 00:00:00',
            'category' => 'entity',
            'action' => 'entity.deleted',
            'prev_hash' => $head['last_hash'],
            'hash' => str_repeat('a', 64),
        ]);

        $result = $reader->verify();
        self::assertFalse($result['valid']);
        self::assertSame((int) $head['last_seq'] + 1, $result['brokenAtSeq']);
    }

    public function testVerificationDetectsTruncation(): void
    {
        $this->tokenFor('admin@example.org', admin: true);
        $this->tokenFor('second@example.org');
        $connection = $this->em()->getConnection();

        $connection->executeStatement('SET @audit_purge_allowed = 1');
        $connection->executeStatement('DELETE FROM audit_log ORDER BY seq DESC LIMIT 1');
        $connection->executeStatement('SET @audit_purge_allowed = NULL');

        $result = static::getContainer()->get(AuditLogReader::class)->verify();
        self::assertFalse($result['valid']);
        self::assertFalse($result['headMatches']);
    }

    public function testHashIsDeterministic(): void
    {
        $row = ['seq' => 1, 'event_id' => 'e', 'occurred_at' => '2026-01-01 00:00:00', 'category' => 'auth', 'action' => 'auth.logout', 'actor_id' => null, 'actor_label' => null, 'subject_type' => null, 'subject_id' => null, 'changes' => null, 'context' => '{}'];

        self::assertSame(AuditHasher::hash(AuditHasher::GENESIS_HASH, $row), AuditHasher::hash(AuditHasher::GENESIS_HASH, $row));
        self::assertNotSame(AuditHasher::hash(AuditHasher::GENESIS_HASH, $row), AuditHasher::hash(AuditHasher::GENESIS_HASH, ['action' => 'auth.login_failed'] + $row));
    }
}
