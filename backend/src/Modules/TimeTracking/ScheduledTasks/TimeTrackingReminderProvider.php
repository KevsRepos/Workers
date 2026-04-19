<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\ScheduledTasks;

use App\Modules\TimeTracking\Repository;
use App\Modules\TimeTracking\ScheduledTasks\NextReminderTimeTrigger;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;

#[AsSchedule('time_tracking_reminder')]
class TimeTrackingReminderProvider implements ScheduleProviderInterface
{
    public function __construct(
        private Repository $repo,
    ) {
    }

    public function getSchedule(): Schedule
    {
        // return $this->schedule ??= new Schedule();
        return (new Schedule())
            ->with(
                RecurringMessage::trigger(
                    new NextReminderTimeTrigger(
                        CronExpressionTrigger::fromSpec('* * * * *'),
                        $this->repo,
                    ),
                    new SendTimeTrackingRemindersMessage()
                )
            );
    }
}