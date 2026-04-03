<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Modules\Account\Account;
use Doctrine\ORM\EntityManagerInterface;

class Factory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function createTimeSheet(string $accountId, int $month, int $year): MonthlyTimeSheet
    {
        $timeSheet = new MonthlyTimeSheet();
        $timeSheet->account = $this->em->getRepository(Account::class)->find($accountId);
        $timeSheet->month = $month;
        $timeSheet->year = $year;

        return $timeSheet;
    }

    public function createEntry(
        MonthlyTimeSheet $timeSheet,
        int $day,
        \DateTimeImmutable $start,
        int $breakDuration,
        \DateTimeImmutable $end
    ): MonthlyTimeSheetEntry {
        $entry = new MonthlyTimeSheetEntry();
        $entry->monthlyTimeSheet = $timeSheet;
        $entry->day = $day;
        $entry->start = $start;
        $entry->breakDuration = $breakDuration;
        $entry->end = $end;

        return $entry;
    }
}
