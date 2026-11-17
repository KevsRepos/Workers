<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Reminder;

use Symfony\Component\Validator\Constraints as Assert;

final class ReminderData
{
    public bool $enabled = false;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^([01]\d|2[0-3]):[0-5]\d$/', message: 'Use the format HH:MM.')]
    public string $time = '17:00';

    /** @var list<int> */
    #[Assert\Unique]
    #[Assert\All([new Assert\Range(min: 1, max: 7)])]
    public array $weekdays = [1, 2, 3, 4, 5];
}
