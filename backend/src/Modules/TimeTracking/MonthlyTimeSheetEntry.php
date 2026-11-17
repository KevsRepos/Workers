<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Shared\Domain\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'monthly_time_sheet_entry')]
#[ORM\UniqueConstraint(name: 'uniq_time_sheet_entry_day', columns: ['time_sheet_id', 'day'])]
class MonthlyTimeSheetEntry extends Entity
{
    #[ORM\ManyToOne(targetEntity: MonthlyTimeSheet::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public MonthlyTimeSheet $timeSheet;

    #[ORM\Column(type: 'smallint')]
    public int $day;

    #[ORM\Column(length: 20, enumType: TimeSheetEntryType::class)]
    public TimeSheetEntryType $type = TimeSheetEntryType::WORK;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $start = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $end = null;

    /** Break in minutes. */
    #[ORM\Column(nullable: true)]
    public ?int $breakMinutes = null;

    #[ORM\Column(length: 500, nullable: true)]
    public ?string $note = null;

    public function date(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $this->timeSheet->year, $this->timeSheet->month, $this->day));
    }

    /**
     * Net working time in minutes (work entries only).
     */
    public function workedMinutes(): int
    {
        if ($this->type !== TimeSheetEntryType::WORK || $this->start === null || $this->end === null) {
            return 0;
        }

        return max(0, self::minutesOfDay($this->end) - self::minutesOfDay($this->start) - ($this->breakMinutes ?? 0));
    }

    public static function minutesOfDay(\DateTimeImmutable $time): int
    {
        return (int) $time->format('G') * 60 + (int) $time->format('i');
    }
}
