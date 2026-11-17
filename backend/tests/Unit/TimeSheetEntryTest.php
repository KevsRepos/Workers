<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Modules\Account\Account;
use App\Modules\TimeTracking\MonthlyTimeSheet;
use App\Modules\TimeTracking\MonthlyTimeSheetEntry;
use App\Modules\TimeTracking\TimeSheetEntryType;
use PHPUnit\Framework\TestCase;

final class TimeSheetEntryTest extends TestCase
{
    public function testWorkedMinutesSubtractBreak(): void
    {
        $entry = new MonthlyTimeSheetEntry();
        $entry->timeSheet = new MonthlyTimeSheet(new Account('a@example.org', 'A', 'B'), 2024, 2);
        $entry->type = TimeSheetEntryType::WORK;
        $entry->start = new \DateTimeImmutable('07:30');
        $entry->end = new \DateTimeImmutable('16:00');
        $entry->breakMinutes = 30;

        self::assertSame(480, $entry->workedMinutes());
        self::assertSame(29, $entry->timeSheet->daysInMonth());
    }

    public function testAbsenceHasNoWorkedMinutes(): void
    {
        $entry = new MonthlyTimeSheetEntry();
        $entry->type = TimeSheetEntryType::VACATION;

        self::assertSame(0, $entry->workedMinutes());
        self::assertTrue($entry->type->isAbsence());
    }
}
