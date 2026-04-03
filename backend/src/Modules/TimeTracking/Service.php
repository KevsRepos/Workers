<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use Error;
use Exception;
use App\Lib\JWTExtractor;
use App\Lib\Success;
use App\Modules\TimeTracking\Dto\CreateMonthlyTimeSheetRequestDto;
use App\Modules\TimeTracking\Dto\CreateMonthlyTimeSheetEntryRequestDto;
use App\Modules\TimeTracking\Dto\UpdateMonthlyTimeSheetEntryRequestDto;
use App\Modules\TimeTracking\Dto\MonthlyTimeSheetResponseDto;
use App\Modules\TimeTracking\Dto\MonthlyTimeSheetEntryResponseDto;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private JWTExtractor $jwtExtractor
    ) {}

    public function createTimeSheet(CreateMonthlyTimeSheetRequestDto $data): Error|Success
    {
        $accountId = $this->jwtExtractor->getUserId();

        $existing = $this->repo->findByAccountMonthYear($accountId, $data->month, $data->year);
        if ($existing) {
            return new Error("TimeSheetAlreadyExists", 400);
        }

        $timeSheet = $this->factory->createTimeSheet($accountId, $data->month, $data->year);

        try {
            $this->repo->save($timeSheet, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("TimeSheetAlreadyExists", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("TimeSheetCreated", ['id' => $timeSheet->id]);
    }

    public function getCurrentTimeSheet(): ?MonthlyTimeSheetResponseDto
    {
        $accountId = $this->jwtExtractor->getUserId();
        $now = new \DateTimeImmutable();

        $currentTimeSheet = $this->repo->findByAccountMonthYear($accountId, (int)$now->format('n'), (int)$now->format('Y'));

        if (!$currentTimeSheet) {
            return null;
        }

        return $this->normalizeMonthlyTimeSheetEntries($currentTimeSheet);
    }

    public function normalizeMonthlyTimeSheetEntries(MonthlyTimeSheet $timeSheet): MonthlyTimeSheetResponseDto
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $timeSheet->month, $timeSheet->year);

        $entriesByDay = [];
        $totalHours = 0.0;
        foreach ($timeSheet->MonthlyTimeSheetEntries as $entry) {
            $workedMinutes = ($entry->end->getTimestamp() - $entry->start->getTimestamp()) / 60 - $entry->breakDuration;
            $entryHours = round($workedMinutes / 60, 2);
            $totalHours += $entryHours;

            $entriesByDay[$entry->day] = new MonthlyTimeSheetEntryResponseDto(
                $entry->id,
                $entry->day,
                $entry->start->format('H:i'),
                $entry->breakDuration,
                $entry->end->format('H:i'),
                $entryHours,
            );
        }

        $now = new \DateTimeImmutable();
        $isCurrentMonth = $timeSheet->month === (int)$now->format('n') && $timeSheet->year === (int)$now->format('Y');
        $maxDay = ($isCurrentMonth ? (int)$now->format('j') : $daysInMonth);

        $entries = [];
        for ($day = 1; $day <= $maxDay; $day++) {
            $entries[] = $entriesByDay[$day] ?? null;
        }

        return new MonthlyTimeSheetResponseDto(
            $timeSheet->id,
            $timeSheet->month,
            $timeSheet->year,
            $entries,
            round($totalHours, 2),
        );
    }

    public function getTimeSheet(string $id): ?MonthlyTimeSheet
    {
        return $this->repo->findById($id);
    }

    public function listByYear(int $year): array
    {
        $accountId = $this->jwtExtractor->getUserId();

        return $this->repo->findByAccountAndYear($accountId, $year);
    }

    public function createEntry(string $timeSheetId, CreateMonthlyTimeSheetEntryRequestDto $data): Error|Success
    {
        $timeSheet = $this->repo->findById($timeSheetId);
        if (!$timeSheet) {
            return new Error("TimeSheetNotFound", 404);
        }

        $entry = $this->factory->createEntry(
            $timeSheet,
            $data->day,
            new \DateTimeImmutable($data->start),
            $data->breakDuration,
            new \DateTimeImmutable($data->end)
        );

        try {
            $this->repo->saveEntry($entry, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("EntryAlreadyExistsForDay", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("EntryCreated", ['id' => $entry->id]);
    }

    public function updateEntry(string $entryId, UpdateMonthlyTimeSheetEntryRequestDto $data): Error|Success
    {
        $entry = $this->repo->findEntryById($entryId);
        if (!$entry) {
            return new Error("EntryNotFound", 404);
        }

        $entry->day = $data->day;
        $entry->start = new \DateTimeImmutable($data->start);
        $entry->breakDuration = $data->breakDuration;
        $entry->end = new \DateTimeImmutable($data->end);

        try {
            $this->repo->saveEntry($entry, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("EntryAlreadyExistsForDay", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("EntryUpdated");
    }

    public function deleteEntry(string $entryId): Error|Success
    {
        $entry = $this->repo->findEntryById($entryId);
        if (!$entry) {
            return new Error("EntryNotFound", 404);
        }

        try {
            $this->repo->removeEntry($entry, true);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("EntryDeleted");
    }
}
