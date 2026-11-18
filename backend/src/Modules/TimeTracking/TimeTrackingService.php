<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Modules\Account\Account;
use App\Modules\TimeTracking\Dto\DayResponse;
use App\Modules\TimeTracking\Dto\EntryData;
use App\Modules\TimeTracking\Dto\EntryResponse;
use App\Modules\TimeTracking\Dto\MonthResponse;
use App\Modules\TimeTracking\Dto\MonthSummary;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\ConflictException;
use App\Shared\Exception\NotFoundException;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class TimeTrackingService
{
    /** ArbZG § 3: maximum daily working time. */
    private const int MAX_DAILY_MINUTES = 600;

    /** ArbZG § 5: minimum uninterrupted rest period between two working days. */
    private const int MIN_REST_MINUTES = 660;

    /** Longer spans are treated as input errors (e.g. swapped times). */
    private const int MAX_SPAN_MINUTES = 960;

    public function __construct(
        private Repository $sheets,
        private ClockInterface $clock,
        #[Autowire('%app.time_tracking.absence_hours%')]
        private float $absenceHours,
    ) {
    }

    /**
     * @return list<int>
     */
    public function years(Account $account): array
    {
        $years = $this->sheets->findYears($account);
        $current = (int) $this->clock->now()->format('Y');
        if (!\in_array($current, $years, true)) {
            array_unshift($years, $current);
        }
        rsort($years);

        return $years;
    }

    /**
     * @return list<MonthSummary>
     */
    public function yearSummary(Account $account, int $year): array
    {
        $this->assertYear($year);
        $byMonth = [];
        foreach ($this->sheets->findForYear($account, $year) as $sheet) {
            $minutes = 0.0;
            foreach ($sheet->entries as $entry) {
                $minutes += $this->hours($entry) * 60;
            }
            $byMonth[$sheet->month] = new MonthSummary($sheet->month, $sheet->entries->count(), round($minutes / 60, 2));
        }

        $result = [];
        for ($month = 1; $month <= 12; ++$month) {
            $result[] = $byMonth[$month] ?? new MonthSummary($month, 0, 0.0);
        }

        return $result;
    }

    public function month(Account $account, int $year, int $month): MonthResponse
    {
        $this->assertYear($year);
        $this->assertMonth($month);
        $sheet = $this->sheets->findForMonth($account, $year, $month) ?? new MonthlyTimeSheet($account, $year, $month);
        $today = $this->clock->now()->setTime(0, 0);

        $days = [];
        $worked = 0.0;
        $absence = 0.0;
        $counts = array_fill_keys(array_map(static fn (TimeSheetEntryType $t): string => $t->value, TimeSheetEntryType::cases()), 0);
        $warningCount = 0;

        for ($day = 1; $day <= $sheet->daysInMonth(); ++$day) {
            $date = new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $year, $month, $day));
            $entry = $sheet->entryForDay($day);
            $entryResponse = null;

            if ($entry !== null) {
                $hours = $this->hours($entry);
                $warnings = $this->warnings($entry);
                $warningCount += \count($warnings);
                $entryResponse = EntryResponse::fromEntity($entry, $hours, $warnings);
                ++$counts[$entry->type->value];
                $entry->type->isAbsence() ? $absence += $hours : $worked += $hours;
            }

            $weekday = (int) $date->format('N');
            $days[] = new DayResponse($day, $date->format('Y-m-d'), $weekday, $weekday >= 6, $date > $today, $entryResponse);
        }

        return new MonthResponse(
            $sheet->id,
            $year,
            $month,
            (string) $account->id,
            $account->displayName,
            $days,
            round($worked, 2),
            round($absence, 2),
            round($worked + $absence, 2),
            $counts,
            $warningCount,
        );
    }

    public function getEntry(string $id): MonthlyTimeSheetEntry
    {
        return $this->sheets->findEntry($id) ?? throw NotFoundException::resource('Time sheet entry', $id);
    }

    public function createEntry(Account $account, int $year, int $month, EntryData $data): MonthlyTimeSheetEntry
    {
        $this->assertYear($year);
        $this->assertMonth($month);

        $sheet = $this->sheets->findForMonth($account, $year, $month);
        if ($sheet === null) {
            $sheet = new MonthlyTimeSheet($account, $year, $month);
            $this->sheets->add($sheet);
        }

        \assert($data->day !== null);
        if ($sheet->entryForDay($data->day) !== null) {
            throw new ConflictException('There is already an entry for this day. Edit the existing entry instead.');
        }

        $entry = new MonthlyTimeSheetEntry();
        $entry->timeSheet = $sheet;
        $this->apply($entry, $data);
        $sheet->entries->add($entry);

        return $entry;
    }

    public function updateEntry(MonthlyTimeSheetEntry $entry, EntryData $data): MonthlyTimeSheetEntry
    {
        \assert($data->day !== null);
        $other = $entry->timeSheet->entryForDay($data->day);
        if ($other !== null && $other !== $entry) {
            throw new ConflictException('There is already an entry for this day.');
        }

        $this->apply($entry, $data);

        return $entry;
    }

    public function deleteEntry(MonthlyTimeSheetEntry $entry): void
    {
        $this->sheets->removeEntry($entry);
    }

    public function hours(MonthlyTimeSheetEntry $entry): float
    {
        return $entry->type->isAbsence() ? $this->absenceHours : round($entry->workedMinutes() / 60, 2);
    }

    /**
     * Hints according to the German Working Hours Act (ArbZG). They do not block saving because
     * the record must reflect the actual working time.
     *
     * @return list<string>
     */
    public function warnings(MonthlyTimeSheetEntry $entry, ?MonthlyTimeSheetEntry $previousDay = null, bool $previousResolved = false): array
    {
        if ($entry->type !== TimeSheetEntryType::WORK || $entry->start === null || $entry->end === null) {
            return [];
        }

        if (!$previousResolved) {
            $previousDay = $this->previousDayEntry($entry);
        }

        $warnings = [];
        $worked = $entry->workedMinutes();
        $break = $entry->breakMinutes ?? 0;

        if ($worked > self::MAX_DAILY_MINUTES) {
            $warnings[] = 'Mehr als 10 Stunden Arbeitszeit (§ 3 ArbZG).';
        }
        if ($worked > 9 * 60 && $break < 45) {
            $warnings[] = 'Bei mehr als 9 Stunden sind mindestens 45 Minuten Pause vorgeschrieben (§ 4 ArbZG).';
        } elseif ($worked > 6 * 60 && $break < 30) {
            $warnings[] = 'Bei mehr als 6 Stunden sind mindestens 30 Minuten Pause vorgeschrieben (§ 4 ArbZG).';
        }

        $rest = $this->restMinutes($previousDay, $entry);
        if ($rest !== null && $rest < self::MIN_REST_MINUTES) {
            $warnings[] = \sprintf('Nur %s Stunden Ruhezeit seit dem Vortag; vorgeschrieben sind mindestens 11 Stunden (§ 5 ArbZG).', number_format($rest / 60, 1, ',', ''));
        }

        if ($entry->date()->format('N') === '7') {
            $warnings[] = 'Sonntagsarbeit: nur im Rahmen der Ausnahmen nach § 10 ArbZG zulässig; Ersatzruhetag innerhalb von zwei Wochen gewähren (§ 11 ArbZG).';
        }

        return $warnings;
    }

    /**
     * Work entry of the calendar day before (may be in the previous month's sheet).
     */
    public function previousDayEntry(MonthlyTimeSheetEntry $entry): ?MonthlyTimeSheetEntry
    {
        $sheet = $entry->timeSheet;
        if ($entry->day > 1) {
            return $sheet->entryForDay($entry->day - 1);
        }

        $previousMonth = $entry->date()->modify('-1 day');
        $previousSheet = $this->sheets->findForMonth($sheet->account, (int) $previousMonth->format('Y'), (int) $previousMonth->format('n'));

        return $previousSheet?->entryForDay((int) $previousMonth->format('j'));
    }

    /**
     * Minutes between the end of the previous day's work and the start of this entry.
     */
    private function restMinutes(?MonthlyTimeSheetEntry $previous, MonthlyTimeSheetEntry $entry): ?int
    {
        if ($previous === null || $previous->type !== TimeSheetEntryType::WORK || $previous->end === null || $entry->start === null) {
            return null;
        }

        $previousEnd = MonthlyTimeSheetEntry::minutesOfDay($previous->end) + ($previous->endsNextDay() ? 24 * 60 : 0);
        $start = 24 * 60 + MonthlyTimeSheetEntry::minutesOfDay($entry->start);

        return $start - $previousEnd;
    }

    private function apply(MonthlyTimeSheetEntry $entry, EntryData $data): void
    {
        \assert($data->day !== null && $data->type !== null);
        $sheet = $entry->timeSheet;

        if ($data->day > $sheet->daysInMonth()) {
            throw BusinessRuleException::forField('day', 'This day does not exist in the selected month.', 'invalid-day');
        }

        $date = new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $sheet->year, $sheet->month, $data->day));
        $today = $this->clock->now()->setTime(0, 0);

        $entry->day = $data->day;
        $entry->type = $data->type;
        $entry->note = $data->note !== null && trim($data->note) !== '' ? trim($data->note) : null;

        if ($data->type->isAbsence()) {
            $entry->start = null;
            $entry->end = null;
            $entry->breakMinutes = null;

            return;
        }

        if ($date > $today) {
            throw BusinessRuleException::forField('day', 'Working time cannot be recorded for future days.', 'future-day');
        }

        $start = \DateTimeImmutable::createFromFormat('!H:i', (string) $data->start);
        $end = \DateTimeImmutable::createFromFormat('!H:i', (string) $data->end);
        if ($start === false || $end === false) {
            throw BusinessRuleException::forField('start', 'Invalid time.', 'invalid-time');
        }

        if (MonthlyTimeSheetEntry::minutesOfDay($end) === MonthlyTimeSheetEntry::minutesOfDay($start)) {
            throw BusinessRuleException::forField('end', 'The end time must differ from the start time.', 'end-equals-start');
        }
        // An end time before the start time means the shift ends on the following day.
        $duration = MonthlyTimeSheetEntry::spanMinutes($start, $end);
        if ($duration > self::MAX_SPAN_MINUTES) {
            throw BusinessRuleException::forField('end', 'The working period exceeds 16 hours. Please check start and end time.', 'implausible-span');
        }
        $break = $data->breakMinutes ?? 0;
        if ($break >= $duration) {
            throw BusinessRuleException::forField('breakMinutes', 'The break must be shorter than the working period.', 'break-too-long');
        }

        $entry->start = $start;
        $entry->end = $end;
        $entry->breakMinutes = $break;
    }

    private function assertYear(int $year): void
    {
        if ($year < 2000 || $year > 2100) {
            throw BusinessRuleException::forField('year', 'Invalid year.', 'invalid-year');
        }
    }

    private function assertMonth(int $month): void
    {
        if ($month < 1 || $month > 12) {
            throw BusinessRuleException::forField('month', 'Invalid month.', 'invalid-month');
        }
    }
}
