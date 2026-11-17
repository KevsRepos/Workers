<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Reminder;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Consumed by the "scheduler_default" transport: bin/console messenger:consume scheduler_default.
 */
#[AsSchedule]
final readonly class ReminderSchedule implements ScheduleProviderInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function getSchedule(): Schedule
    {
        return new Schedule()
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            ->add(RecurringMessage::every('5 minutes', new SendDueRemindersMessage()))
            ->add(RecurringMessage::cron('17 3 * * *', new \App\Shared\Maintenance\DailyMaintenanceMessage()));
    }
}
