<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Audit\AuditAction;

final class TimeTrackingTest extends ApiTestCase
{
    private function lastMonth(): array
    {
        $date = new \DateTimeImmutable('first day of last month');

        return [(int) $date->format('Y'), (int) $date->format('n')];
    }

    public function testRecordWorkAndAbsencesWithTotalsAndWarnings(): void
    {
        $token = $this->tokenFor();
        [$year, $month] = $this->lastMonth();

        $work = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 3, 'type' => 'work', 'start' => '07:00', 'end' => '17:30', 'breakMinutes' => 30], $token);
        $this->assertStatus(201);
        self::assertSame(10.0, (float) $work['hours']);
        self::assertCount(1, $work['warnings'], 'Missing 45 min break after 9 h');

        $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 4, 'type' => 'vacation'], $token);
        $this->assertStatus(201);

        $view = $this->request('GET', "/api/v1/time-sheets/$year/$month", token: $token);
        $this->assertStatus(200);
        self::assertSame(10.0, (float) $view['workedHours']);
        self::assertSame(8.0, (float) $view['absenceHours']);
        self::assertSame(18.0, (float) $view['totalHours']);
        self::assertSame(1, $view['countsByType']['vacation']);
        self::assertSame('work', $view['days'][2]['entry']['type']);
        self::assertNull($view['days'][0]['entry']);

        $year = $this->request('GET', "/api/v1/time-sheets/$year", token: $token);
        self::assertCount(12, $year);
        self::assertSame(2, $year[$month - 1]['entries']);
    }

    public function testValidationRules(): void
    {
        $token = $this->tokenFor();
        [$year, $month] = $this->lastMonth();
        $future = new \DateTimeImmutable('first day of next month');

        $endBeforeStart = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 1, 'type' => 'work', 'start' => '12:00', 'end' => '08:00', 'breakMinutes' => 0], $token);
        $this->assertProblem(422, 'business-rule-violation', $endBeforeStart);

        $breakTooLong = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 1, 'type' => 'work', 'start' => '08:00', 'end' => '09:00', 'breakMinutes' => 60], $token);
        $this->assertProblem(422, 'business-rule-violation', $breakTooLong);

        $missingTimes = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 1, 'type' => 'work'], $token);
        $this->assertProblem(422, 'validation-failed', $missingTimes);

        $badFormat = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 1, 'type' => 'work', 'start' => '8 Uhr', 'end' => '17:00'], $token);
        $this->assertProblem(422, 'validation-failed', $badFormat);

        $futureWork = $this->request('POST', \sprintf('/api/v1/time-sheets/%d/%d/entries', $future->format('Y'), $future->format('n')), ['day' => 1, 'type' => 'work', 'start' => '08:00', 'end' => '16:00'], $token);
        $this->assertProblem(422, 'business-rule-violation', $futureWork);

        $futureVacation = $this->request('POST', \sprintf('/api/v1/time-sheets/%d/%d/entries', $future->format('Y'), $future->format('n')), ['day' => 1, 'type' => 'vacation'], $token);
        $this->assertStatus(201);
        self::assertSame('vacation', $futureVacation['type']);

        $invalidDay = $this->request('POST', '/api/v1/time-sheets/2025/2/entries', ['day' => 30, 'type' => 'holiday'], $token);
        $this->assertProblem(422, 'business-rule-violation', $invalidDay);

        $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 2, 'type' => 'sick_leave'], $token);
        $duplicate = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 2, 'type' => 'vacation'], $token);
        $this->assertProblem(409, 'conflict', $duplicate);
    }

    public function testEmployeesCannotTouchOtherTimeSheets(): void
    {
        [$year, $month] = $this->lastMonth();
        $ownerToken = $this->tokenFor('owner@example.org');
        $intruderToken = $this->tokenFor('intruder@example.org');
        $owner = $this->request('GET', '/api/v1/auth/me', token: $ownerToken);

        $entry = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 5, 'type' => 'work', 'start' => '08:00', 'end' => '12:00'], $ownerToken);

        $this->request('PUT', '/api/v1/time-sheets/entries/' . $entry['id'], ['day' => 5, 'type' => 'work', 'start' => '08:00', 'end' => '18:00', 'breakMinutes' => 60], $intruderToken);
        $this->assertStatus(403);
        $this->request('DELETE', '/api/v1/time-sheets/entries/' . $entry['id'], token: $intruderToken);
        $this->assertStatus(403);
        $this->request('GET', "/api/v1/time-sheets/$year/$month?accountId=" . $owner['id'], token: $intruderToken);
        $this->assertStatus(403);
        $this->request('POST', "/api/v1/time-sheets/$year/$month/entries?accountId=" . $owner['id'], ['day' => 6, 'type' => 'vacation'], $intruderToken);
        $this->assertStatus(403);

        self::assertSame(1, (int) $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM monthly_time_sheet_entry'));
    }

    public function testAdminCanCorrectEmployeeEntriesWithAuditTrail(): void
    {
        [$year, $month] = $this->lastMonth();
        $employeeToken = $this->tokenFor('employee@example.org');
        $adminToken = $this->tokenFor('admin@example.org', admin: true);
        $employee = $this->request('GET', '/api/v1/auth/me', token: $employeeToken);

        $entry = $this->request('POST', "/api/v1/time-sheets/$year/$month/entries", ['day' => 5, 'type' => 'work', 'start' => '08:00', 'end' => '12:00'], $employeeToken);

        $view = $this->request('GET', "/api/v1/time-sheets/$year/$month?accountId=" . $employee['id'], token: $adminToken);
        $this->assertStatus(200);
        self::assertSame('Erika Mustermann', $view['accountName']);

        $this->request('PUT', '/api/v1/time-sheets/entries/' . $entry['id'], ['day' => 5, 'type' => 'work', 'start' => '08:00', 'end' => '13:00'], $adminToken);
        $this->assertStatus(200);

        $this->request('DELETE', '/api/v1/time-sheets/entries/' . $entry['id'], token: $adminToken);
        $this->assertStatus(204);

        $updates = $this->auditRows(AuditAction::ENTITY_UPDATED, 'MonthlyTimeSheetEntry');
        self::assertCount(1, $updates);
        self::assertSame('admin@example.org', $updates[0]['actor_label']);
        self::assertStringContainsString('"end"', (string) $updates[0]['changes']);

        $deletions = $this->auditRows(AuditAction::ENTITY_DELETED, 'MonthlyTimeSheetEntry');
        self::assertCount(1, $deletions);
        self::assertStringContainsString('"old"', (string) $deletions[0]['changes'], 'Deleted values are preserved in the audit log');
    }

    public function testReminderSettings(): void
    {
        $token = $this->tokenFor();

        $default = $this->request('GET', '/api/v1/time-tracking/reminder', token: $token);
        self::assertFalse($default['enabled']);

        $saved = $this->request('PUT', '/api/v1/time-tracking/reminder', ['enabled' => true, 'time' => '16:45', 'weekdays' => [5, 1, 3]], $token);
        $this->assertStatus(200);
        self::assertSame(['enabled' => true, 'time' => '16:45', 'weekdays' => [1, 3, 5]], $saved);

        $invalid = $this->request('PUT', '/api/v1/time-tracking/reminder', ['enabled' => true, 'time' => '25:00', 'weekdays' => [8]], $token);
        $this->assertProblem(422, 'validation-failed', $invalid);
    }
}
