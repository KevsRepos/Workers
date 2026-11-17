<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * Computes the tamper-evident hash chain: hash(n) = SHA-256(hash(n-1) || canonical(row n)).
 *
 * The canonical form only uses values exactly as stored in the database so that verification
 * can be reproduced independently (e.g. by an auditor with read access to the table).
 */
final class AuditHasher
{
    public const string GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    /**
     * @param array{seq: int|string, event_id: string, occurred_at: string, category: string, action: string, actor_id: ?string, actor_label: ?string, subject_type: ?string, subject_id: ?string, changes: ?string, context: ?string} $row
     */
    public static function hash(string $previousHash, array $row): string
    {
        $canonical = json_encode([
            (int) $row['seq'],
            $row['event_id'],
            $row['occurred_at'],
            $row['category'],
            $row['action'],
            $row['actor_id'],
            $row['actor_label'],
            $row['subject_type'],
            $row['subject_id'],
            $row['changes'],
            $row['context'],
        ], \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);

        return hash('sha256', $previousHash . $canonical);
    }

    /**
     * Deterministic JSON encoding (recursively sorted object keys).
     */
    public static function canonicalJson(mixed $value): string
    {
        return json_encode(self::sortKeys($value), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR);
    }

    private static function sortKeys(mixed $value): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        if (!array_is_list($value)) {
            ksort($value, \SORT_STRING);
        }

        return array_map(self::sortKeys(...), $value);
    }
}
