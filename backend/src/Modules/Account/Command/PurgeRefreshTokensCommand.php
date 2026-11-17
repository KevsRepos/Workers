<?php

declare(strict_types=1);

namespace App\Modules\Account\Command;

use App\Modules\Account\RefreshToken\RefreshTokenService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:auth:purge-refresh-tokens', description: 'Delete expired and revoked refresh tokens (data minimisation)')]
final class PurgeRefreshTokensCommand extends Command
{
    public function __construct(private readonly RefreshTokenService $refreshTokens)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\sprintf('Deleted %d refresh tokens.', $this->refreshTokens->purgeExpired()));

        return Command::SUCCESS;
    }
}
