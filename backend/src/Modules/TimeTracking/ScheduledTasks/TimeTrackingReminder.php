<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\ScheduledTasks;

class TimeTrackingReminder
{
    public function __construct(private int $id) {}

    public function getId(): int
    {
        return $this->id;
    }
}