<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Command;

use App\Audit\AuditAction;
use App\Audit\AuditTrail;
use Doctrine\DBAL\Connection;
use Psr\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Deletes time records after the retention period (GDPR Art. 5(1)(e), Art. 17).
 *
 * ArbZG § 16 Abs. 2 requires keeping records for at least two years. With the default of two
 * years, records of 2024 are kept until the end of 2026 and deleted from 2027 on. Increase the
 * retention if the records also serve as payroll documents.
 */
#[AsCommand(name: 'app:time-tracking:purge', description: 'Delete time records older than the retention period')]
final class PurgeTimeSheetsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AuditTrail $auditTrail,
        private readonly ClockInterface $clock,
        #[Autowire('%app.time_tracking.retention_years%')]
        private readonly int $retentionYears,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report what would be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $retention = max(2, $this->retentionYears);
        $cutoffYear = (int) $this->clock->now()->format('Y') - $retention;

        $sheets = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM monthly_time_sheet WHERE year < ?', [$cutoffYear]);
        $entries = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM monthly_time_sheet_entry e JOIN monthly_time_sheet s ON s.id = e.time_sheet_id WHERE s.year < ?', [$cutoffYear]);

        if ($input->getOption('dry-run')) {
            $io->note(\sprintf('%d time sheets with %d entries from before %d would be deleted.', $sheets, $entries, $cutoffYear));

            return Command::SUCCESS;
        }

        $this->connection->transactional(function (Connection $connection) use ($cutoffYear, $sheets, $entries, $retention): void {
            // Entries are removed by ON DELETE CASCADE.
            $connection->executeStatement('DELETE FROM monthly_time_sheet WHERE year < ?', [$cutoffYear]);
            $this->auditTrail->record(AuditAction::RETENTION_PURGED, 'MonthlyTimeSheet', null, [
                'beforeYear' => $cutoffYear,
                'retentionYears' => $retention,
                'sheets' => $sheets,
                'entries' => $entries,
            ]);
        });

        $io->success(\sprintf('Deleted %d time sheets with %d entries from before %d.', $sheets, $entries, $cutoffYear));

        return Command::SUCCESS;
    }
}
