<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking;

enum TimeSheetEntryType: string
{
    case WORK = 'work';
    case HOLIDAY = 'holiday';
    case SICK_LEAVE = 'sick_leave';
    case VACATION = 'vacation';

    public function isAbsence(): bool
    {
        return $this !== self::WORK;
    }

    public function label(): string
    {
        return match ($this) {
            self::WORK => 'Arbeit',
            self::HOLIDAY => 'Feiertag',
            self::SICK_LEAVE => 'Krank',
            self::VACATION => 'Urlaub',
        };
    }
}
