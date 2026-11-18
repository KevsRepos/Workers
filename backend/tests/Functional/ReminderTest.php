<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Modules\TimeTracking\Reminder\SendDueRemindersHandler;
use App\Modules\TimeTracking\Reminder\SendDueRemindersMessage;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

final class ReminderTest extends ApiTestCase
{
    protected function tearDown(): void
    {
        Clock::set(new \Symfony\Component\Clock\NativeClock());
        parent::tearDown();
    }

    public function testReminderIsSentOnceWhenNoEntryExists(): void
    {
        $token = $this->tokenFor('employee@example.org');
        $this->request('PUT', '/api/v1/time-tracking/reminder', ['enabled' => true, 'time' => '17:00', 'weekdays' => [1, 2, 3, 4, 5]], $token);
        $this->assertStatus(200);

        // Wednesday 18:00 → due.
        Clock::set(new MockClock('2026-11-18 18:00:00', 'Europe/Berlin'));
        $handler = static::getContainer()->get(SendDueRemindersHandler::class);

        $handler(new SendDueRemindersMessage());
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertNotNull($email);
        self::assertEmailAddressContains($email, 'To', 'employee@example.org');

        $handler(new SendDueRemindersMessage());
        self::assertEmailCount(1, message: 'Only one reminder per day');
    }

    public function testNoReminderBeforeTimeOrOnDisabledWeekday(): void
    {
        $token = $this->tokenFor('employee@example.org');
        $this->request('PUT', '/api/v1/time-tracking/reminder', ['enabled' => true, 'time' => '17:00', 'weekdays' => [1]], $token);

        $handler = static::getContainer()->get(SendDueRemindersHandler::class);
        Clock::set(new MockClock('2026-11-16 16:00:00', 'Europe/Berlin')); // Monday, too early
        $handler(new SendDueRemindersMessage());
        Clock::set(new MockClock('2026-11-18 18:00:00', 'Europe/Berlin')); // Wednesday, not selected
        $handler(new SendDueRemindersMessage());

        self::assertEmailCount(0);
    }
}
