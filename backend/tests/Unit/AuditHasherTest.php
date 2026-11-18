<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Audit\AuditHasher;
use PHPUnit\Framework\TestCase;

final class AuditHasherTest extends TestCase
{
    public function testCanonicalJsonSortsKeysRecursively(): void
    {
        self::assertSame(
            '{"a":{"x":1,"y":[3,1]},"b":"ä"}',
            AuditHasher::canonicalJson(['b' => 'ä', 'a' => ['y' => [3, 1], 'x' => 1]]),
        );
    }

    public function testCanonicalJsonPreservesFloats(): void
    {
        self::assertSame('{"q":1.0}', AuditHasher::canonicalJson(['q' => 1.0]));
    }
}
