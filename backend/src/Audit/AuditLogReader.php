<?php

declare(strict_types=1);

namespace App\Audit;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Read access and integrity verification for the audit log.
 */
final readonly class AuditLogReader
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param array{action?: ?string, category?: ?string, actorId?: ?string, subjectType?: ?string, subjectId?: ?string, from?: ?\DateTimeImmutable, to?: ?\DateTimeImmutable} $filter
     *
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function search(array $filter, int $page, int $limit): array
    {
        $qb = $this->connection->createQueryBuilder()->from('audit_log');
        $this->applyFilter($qb, $filter);

        $total = (int) (clone $qb)->select('COUNT(*)')->executeQuery()->fetchOne();

        $rows = $qb
            ->select('seq', 'event_id', 'occurred_at', 'category', 'action', 'actor_id', 'actor_label', 'subject_type', 'subject_id', 'changes', 'context', 'hash')
            ->orderBy('seq', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();

        return ['items' => array_map(self::present(...), $rows), 'total' => $total];
    }

    /**
     * Recomputes the hash chain. A purge keeps the chain verifiable from the first retained row
     * (its prev_hash acts as anchor, documented by the "audit.purged" event).
     *
     * @return array{valid: bool, checked: int, firstSeq: ?int, lastSeq: ?int, headMatches: bool, brokenAtSeq: ?int, reason: ?string}
     */
    public function verify(): array
    {
        $result = $this->connection->executeQuery('SELECT * FROM audit_log ORDER BY seq ASC');

        $expectedPrev = null;
        $expectedSeq = null;
        $checked = 0;
        $firstSeq = null;
        $lastSeq = null;
        $lastHash = AuditHasher::GENESIS_HASH;

        while (($row = $result->fetchAssociative()) !== false) {
            $seq = (int) $row['seq'];
            $firstSeq ??= $seq;

            if ($expectedSeq !== null && $seq !== $expectedSeq) {
                return $this->failure($checked, $firstSeq, $lastSeq, $seq, 'Gap in sequence numbers (deleted rows).');
            }
            if ($expectedPrev !== null && $row['prev_hash'] !== $expectedPrev) {
                return $this->failure($checked, $firstSeq, $lastSeq, $seq, 'Previous-hash link broken.');
            }
            if ($firstSeq === $seq && $seq === 1 && $row['prev_hash'] !== AuditHasher::GENESIS_HASH) {
                return $this->failure($checked, $firstSeq, $lastSeq, $seq, 'First entry does not start from genesis.');
            }

            /** @var array{seq: int|string, event_id: string, occurred_at: string, category: string, action: string, actor_id: ?string, actor_label: ?string, subject_type: ?string, subject_id: ?string, changes: ?string, context: ?string} $row */
            $computed = AuditHasher::hash((string) $row['prev_hash'], $row);
            if (!hash_equals($computed, (string) $row['hash'])) {
                return $this->failure($checked, $firstSeq, $lastSeq, $seq, 'Content hash mismatch (row was modified).');
            }

            $expectedPrev = $computed;
            $expectedSeq = $seq + 1;
            $lastSeq = $seq;
            $lastHash = $computed;
            ++$checked;
        }

        /** @var array{last_seq: int|string, last_hash: string}|false $head */
        $head = $this->connection->fetchAssociative('SELECT last_seq, last_hash FROM audit_chain_head WHERE id = 1');
        $headMatches = $head !== false && (int) $head['last_seq'] === ($lastSeq ?? 0) && hash_equals($head['last_hash'], $lastHash);

        return [
            'valid' => $headMatches,
            'checked' => $checked,
            'firstSeq' => $firstSeq,
            'lastSeq' => $lastSeq,
            'headMatches' => $headMatches,
            'brokenAtSeq' => null,
            'reason' => $headMatches ? null : 'Chain head does not match the last entry (trailing rows deleted?).',
        ];
    }

    /**
     * @return array{valid: bool, checked: int, firstSeq: ?int, lastSeq: ?int, headMatches: bool, brokenAtSeq: ?int, reason: ?string}
     */
    private function failure(int $checked, ?int $firstSeq, ?int $lastSeq, int $seq, string $reason): array
    {
        return ['valid' => false, 'checked' => $checked, 'firstSeq' => $firstSeq, 'lastSeq' => $lastSeq, 'headMatches' => false, 'brokenAtSeq' => $seq, 'reason' => $reason];
    }

    /**
     * @param array{action?: ?string, category?: ?string, actorId?: ?string, subjectType?: ?string, subjectId?: ?string, from?: ?\DateTimeImmutable, to?: ?\DateTimeImmutable} $filter
     */
    private function applyFilter(QueryBuilder $qb, array $filter): void
    {
        foreach (['action' => 'action', 'category' => 'category', 'actorId' => 'actor_id', 'subjectType' => 'subject_type', 'subjectId' => 'subject_id'] as $key => $column) {
            if (($filter[$key] ?? null) !== null && $filter[$key] !== '') {
                $qb->andWhere(\sprintf('%s = :%s', $column, $key))->setParameter($key, $filter[$key]);
            }
        }
        $utc = new \DateTimeZone('UTC');
        if (($filter['from'] ?? null) instanceof \DateTimeImmutable) {
            $qb->andWhere('occurred_at >= :from')->setParameter('from', $filter['from']->setTimezone($utc)->format('Y-m-d H:i:s'));
        }
        if (($filter['to'] ?? null) instanceof \DateTimeImmutable) {
            $qb->andWhere('occurred_at <= :to')->setParameter('to', $filter['to']->setTimezone($utc)->format('Y-m-d H:i:s'));
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private static function present(array $row): array
    {
        return [
            'seq' => (int) $row['seq'],
            'eventId' => $row['event_id'],
            'occurredAt' => (new \DateTimeImmutable((string) $row['occurred_at'], new \DateTimeZone('UTC')))->format(\DATE_ATOM),
            'category' => $row['category'],
            'action' => $row['action'],
            'actorId' => $row['actor_id'],
            'actorLabel' => $row['actor_label'],
            'subjectType' => $row['subject_type'],
            'subjectId' => $row['subject_id'],
            'changes' => $row['changes'] !== null ? json_decode((string) $row['changes'], true, flags: \JSON_THROW_ON_ERROR) : null,
            'context' => $row['context'] !== null ? json_decode((string) $row['context'], true, flags: \JSON_THROW_ON_ERROR) : null,
            'hash' => $row['hash'],
        ];
    }
}
