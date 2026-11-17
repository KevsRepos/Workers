<?php

declare(strict_types=1);

namespace App\Modules\Account\Command;

use App\Modules\Account\AccountService;
use App\Modules\Account\Dto\CreateAccountRequest;
use App\Modules\Account\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Creates an account, e.g. the initial administrator after deployment.
 * The password is read from STDIN (--password-stdin) or prompted interactively; it is never passed as argument.
 */
#[AsCommand(name: 'app:account:create', description: 'Create a user account')]
final class CreateAccountCommand extends Command
{
    public function __construct(
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
            ->addArgument('first-name', InputArgument::REQUIRED)
            ->addArgument('surname', InputArgument::REQUIRED)
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant administrator role')
            ->addOption('password-stdin', null, InputOption::VALUE_NONE, 'Read the password from STDIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $request = new CreateAccountRequest();
        $request->emailAddress = (string) $input->getArgument('email');
        $request->firstName = (string) $input->getArgument('first-name');
        $request->surname = (string) $input->getArgument('surname');
        $request->roles = $input->getOption('admin') ? [Role::USER, Role::ADMIN] : [Role::USER];
        $request->password = $input->getOption('password-stdin')
            ? rtrim((string) stream_get_contents(\STDIN), "\r\n")
            : (string) $io->askHidden('Password (min. 12 characters)');

        $violations = $this->validator->validate($request);
        if (\count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error(\sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return Command::INVALID;
        }

        $account = $this->entityManager->wrapInTransaction(fn () => $this->accounts->create($request));
        $io->success(\sprintf('Account %s created (id %s).', $account->emailAddress, $account->id));

        return Command::SUCCESS;
    }
}
