<?php

declare(strict_types=1);

namespace App\Shared\Numbering;

use Doctrine\DBAL\Connection;

/**
 * Gap-free, per-year document numbers (GoBD: unique and traceable numbering).
 *
 * The counter row is locked with SELECT … FOR UPDATE, so numbers are only consumed when the
 * surrounding transaction commits.
 */
final readonly class DocumentNumberGenerator
{
    public function __construct(private Connection $connection)
    {
    }

    public function next(string $sequence, string $prefix, int $year, int $padding = 5): string
    {
        $allocate = function () use ($sequence, $year): int {
            $this->connection->executeStatement(
                'INSERT IGNORE INTO document_sequence (name, year, last_value) VALUES (?, ?, 0)',
                [$sequence, $year],
            );
            $current = (int) $this->connection->fetchOne(
                'SELECT last_value FROM document_sequence WHERE name = ? AND year = ? FOR UPDATE',
                [$sequence, $year],
            );
            $this->connection->executeStatement(
                'UPDATE document_sequence SET last_value = ? WHERE name = ? AND year = ?',
                [$current + 1, $sequence, $year],
            );

            return $current + 1;
        };

        $value = $this->connection->isTransactionActive() ? $allocate() : $this->connection->transactional($allocate);

        return \sprintf('%s-%d-%s', $prefix, $year, str_pad((string) $value, $padding, '0', \STR_PAD_LEFT));
    }
}
