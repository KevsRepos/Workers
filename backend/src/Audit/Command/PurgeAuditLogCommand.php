<?php

declare(strict_types=1);

namespace App\Audit\Command;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Deletes audit entries older than the retention period (oldest first, so the remaining chain
 * stays verifiable). The purge itself is recorded as an audit event.
 */
#[AsCommand(name: 'app:audit:purge', description: 'Delete audit log entries older than the retention period')]
final class PurgeAuditLogCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AuditTrail $auditTrail,
        #[Autowire('%app.audit.retention_days%')]
        private readonly int $retentionDays,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report how many entries would be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cutoff = new \DateTimeImmutable(\sprintf('-%d days', $this->retentionDays), new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        $maxSeq = $this->connection->fetchOne('SELECT MAX(seq) FROM audit_log WHERE occurred_at < ?', [$cutoff]);
        if ($maxSeq === null || $maxSeq === false) {
            $io->success('Nothing to purge.');

            return Command::SUCCESS;
        }

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM audit_log WHERE seq <= ?', [$maxSeq]);
        if ($input->getOption('dry-run')) {
            $io->note(\sprintf('%d entries (up to seq %d) would be deleted.', $count, $maxSeq));

            return Command::SUCCESS;
        }

        $this->connection->transactional(function (Connection $connection) use ($maxSeq, $count, $cutoff): void {
            $connection->executeStatement('SET @audit_purge_allowed = 1');
            try {
                $connection->executeStatement('DELETE FROM audit_log WHERE seq <= ?', [$maxSeq]);
            } finally {
                $connection->executeStatement('SET @audit_purge_allowed = NULL');
            }
            $this->auditTrail->record(AuditAction::AUDIT_PURGED, data: ['deleted' => $count, 'upToSeq' => (int) $maxSeq, 'cutoff' => $cutoff, 'retentionDays' => $this->retentionDays]);
        });

        $io->success(\sprintf('Deleted %d audit entries up to seq %d.', $count, $maxSeq));

        return Command::SUCCESS;
    }
}
