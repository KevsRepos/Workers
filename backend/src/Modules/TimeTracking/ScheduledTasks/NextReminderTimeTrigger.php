<?php declare(strict_types=1);

namespace App\Modules\TimeTracking\ScheduledTasks;

use App\Modules\TimeTracking\Repository;
use DateTimeImmutable;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

class NextReminderTimeTrigger implements TriggerInterface
{
    public function __construct(
        private TriggerInterface $inner,
        private Repository $repo,
    ) {
    }

    public function __toString(): string
    {
        return $this->inner . '';
    }

    public function getNextRunDate(DateTimeImmutable $run,): ?DateTimeImmutable
    {
        $notifications = $this->repo->findAllNotifications();

        if (empty($notifications)) {
            return null;
        }

        $dayMap = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        $earliest = null;

        foreach ($notifications as $notification) {
            for ($offset = 0; $offset <= 7; $offset++) {
                $candidate = $run->modify("+{$offset} days");
                $dayOfWeek = (int) $candidate->format('N');
                $dayProperty = $dayMap[$dayOfWeek];

                if (!$notification->$dayProperty) {
                    continue;
                }

                $candidateDate = $candidate->setTime(
                    (int) $notification->notificationTime->format('H'),
                    (int) $notification->notificationTime->format('i'),
                    (int) $notification->notificationTime->format('s'),
                );

                if ($candidateDate <= $run) {
                    continue;
                }

                if ($earliest === null || $candidateDate < $earliest) {
                    $earliest = $candidateDate;
                }

                break;
            }
        }

        return $earliest;
    }
}