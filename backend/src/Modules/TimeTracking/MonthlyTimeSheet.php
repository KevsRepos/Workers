<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Modules\Account\Account;
use App\Shared\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'monthly_time_sheet')]
#[ORM\UniqueConstraint(name: 'uniq_time_sheet_account_month', columns: ['account_id', 'year', 'month'])]
class MonthlyTimeSheet extends Entity
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Account $account;

    #[ORM\Column(type: 'smallint')]
    public int $year;

    #[ORM\Column(type: 'smallint')]
    public int $month;

    /** @var Collection<int, MonthlyTimeSheetEntry> */
    #[ORM\OneToMany(targetEntity: MonthlyTimeSheetEntry::class, mappedBy: 'timeSheet', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['day' => 'ASC'])]
    public Collection $entries;

    public function __construct(Account $account, int $year, int $month)
    {
        $this->account = $account;
        $this->year = $year;
        $this->month = $month;
        $this->entries = new ArrayCollection();
    }

    public function daysInMonth(): int
    {
        return (int) new \DateTimeImmutable(\sprintf('%04d-%02d-01', $this->year, $this->month))->format('t');
    }

    public function entryForDay(int $day): ?MonthlyTimeSheetEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->day === $day) {
                return $entry;
            }
        }

        return null;
    }
}
