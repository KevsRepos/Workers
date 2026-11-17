<?php

declare(strict_types=1);

namespace App\Audit\Command;

use App\Audit\AuditLogReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:audit:verify', description: 'Verify the integrity of the hash-chained audit log')]
final class VerifyAuditLogCommand extends Command
{
    public function __construct(private readonly AuditLogReader $reader)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->reader->verify();

        $io->definitionList(
            ['Entries checked' => (string) $result['checked']],
            ['Sequence range' => \sprintf('%s – %s', $result['firstSeq'] ?? '-', $result['lastSeq'] ?? '-')],
            ['Head matches' => $result['headMatches'] ? 'yes' : 'no'],
        );

        if (!$result['valid']) {
            $io->error(\sprintf('Audit log integrity violated%s: %s', $result['brokenAtSeq'] !== null ? ' at seq ' . $result['brokenAtSeq'] : '', $result['reason']));

            return Command::FAILURE;
        }

        $io->success('Audit log integrity verified.');

        return Command::SUCCESS;
    }
}
