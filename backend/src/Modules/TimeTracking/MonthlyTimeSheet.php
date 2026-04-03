<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Lib\Entity;
use App\Modules\Account\Account;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: "unique_account_month_year", columns: ["account_id", "month", "year"])]
class MonthlyTimeSheet extends Entity
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: "account_id", referencedColumnName: "id")]
    public Account $account;

    #[ORM\Column]
    public int $month;

    #[ORM\Column]
    public int $year;

    #[ORM\OneToMany(mappedBy: "monthlyTimeSheet", targetEntity: MonthlyTimeSheetEntry::class, cascade: ["persist", "remove"])]
    #[Ignore]
    public Collection $MonthlyTimeSheetEntries;
}
