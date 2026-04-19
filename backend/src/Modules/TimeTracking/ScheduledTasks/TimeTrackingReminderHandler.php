<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\ScheduledTasks;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TimeTrackingReminderHandler
{
    public function __invoke(TimeTrackingReminder $message)
    {
        // ... do some work to send the report to the customers
    }
}