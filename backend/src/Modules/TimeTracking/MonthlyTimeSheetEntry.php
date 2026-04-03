<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: "unique_entry_per_day", columns: ["monthly_time_sheet_id", "day"])]
class MonthlyTimeSheetEntry extends Entity
{
    #[ORM\ManyToOne(targetEntity: MonthlyTimeSheet::class, inversedBy: "MonthlyTimeSheetEntries")]
    #[ORM\JoinColumn(name: "monthly_time_sheet_id", referencedColumnName: "id")]
    #[Ignore]
    public MonthlyTimeSheet $monthlyTimeSheet;

    #[ORM\Column]
    public int $day;

    #[ORM\Column(type: "time_immutable")]
    public \DateTimeImmutable $start;

    #[ORM\Column]
    public int $breakDuration;

    #[ORM\Column(type: "time_immutable")]
    public \DateTimeImmutable $end;
}
