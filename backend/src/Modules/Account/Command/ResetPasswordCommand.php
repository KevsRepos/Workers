<?php

declare(strict_types=1);

namespace App\Modules\Account\Command;

use App\Modules\Account\AccountService;
use App\Modules\Account\Dto\ResetPasswordRequest;
use App\Modules\Account\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'app:account:reset-password', description: 'Set a new password for an account and end all its sessions')]
final class ResetPasswordCommand extends Command
{
    public function __construct(
        private readonly Repository $repository,
        private readonly AccountService $accounts,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addOption('password-stdin', null, InputOption::VALUE_NONE, 'Read the password from STDIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $account = $this->repository->findByEmail((string) $input->getArgument('email'));
        if ($account === null) {
            $io->error('Account not found.');

            return Command::FAILURE;
        }

        $request = new ResetPasswordRequest();
        $request->password = $input->getOption('password-stdin')
            ? rtrim((string) stream_get_contents(\STDIN), "\r\n")
            : (string) $io->askHidden('New password (min. 12 characters)');

        $violations = $this->validator->validate($request);
        if (\count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error((string) $violation->getMessage());
            }

            return Command::INVALID;
        }

        $this->entityManager->wrapInTransaction(fn () => $this->accounts->resetPassword($account, $request->password));
        $io->success('Password updated, all sessions revoked.');

        return Command::SUCCESS;
    }
}
