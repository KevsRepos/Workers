<?php

declare(strict_types=1);

namespace App\Audit;

use Doctrine\DBAL\Connection;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * Appends events to the append-only, hash-chained audit_log table.
 *
 * Appends are serialised through a row lock on audit_chain_head. When called inside a running
 * transaction the audit rows commit or roll back together with the business data.
 */
final readonly class AuditWriter
{
    public function __construct(
        private Connection $connection,
        private ClockInterface $clock,
        #[Autowire(service: 'monolog.logger.audit')]
        private LoggerInterface $auditLogger,
    ) {
    }

    /**
     * @param list<AuditEvent> $events
     */
    public function append(array $events): void
    {
        if ($events === []) {
            return;
        }

        if ($this->connection->isTransactionActive()) {
            $this->doAppend($events);

            return;
        }

        $this->connection->transactional(fn () => $this->doAppend($events));
    }

    /**
     * @param list<AuditEvent> $events
     */
    private function doAppend(array $events): void
    {
        /** @var array{last_seq: int|string, last_hash: string}|false $head */
        $head = $this->connection->fetchAssociative('SELECT last_seq, last_hash FROM audit_chain_head WHERE id = 1 FOR UPDATE');
        if ($head === false) {
            throw new \RuntimeException('Audit chain head is missing. Run the database migrations.');
        }

        $seq = (int) $head['last_seq'];
        $hash = $head['last_hash'];
        $occurredAt = $this->clock->now()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        foreach ($events as $event) {
            ++$seq;
            $row = [
                'seq' => $seq,
                'event_id' => Uuid::v7()->toRfc4122(),
                'occurred_at' => $occurredAt,
                'category' => AuditAction::category($event->action),
                'action' => $event->action,
                'actor_id' => $event->actorId,
                'actor_label' => $event->actorLabel !== null ? mb_substr($event->actorLabel, 0, 255) : null,
                'subject_type' => $event->subjectType,
                'subject_id' => $event->subjectId,
                'changes' => $event->changes !== null ? AuditHasher::canonicalJson($event->changes) : null,
                'context' => AuditHasher::canonicalJson($event->context),
            ];
            $row['prev_hash'] = $hash;
            $row['hash'] = $hash = AuditHasher::hash($hash, $row);

            $this->connection->insert('audit_log', $row);

            $this->auditLogger->info($event->action, [
                'seq' => $seq,
                'event_id' => $row['event_id'],
                'actor_id' => $event->actorId,
                'subject_type' => $event->subjectType,
                'subject_id' => $event->subjectId,
            ]);
        }

        $this->connection->executeStatement('UPDATE audit_chain_head SET last_seq = ?, last_hash = ? WHERE id = 1', [$seq, $hash]);
    }
}
