<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Reminder;

use App\Audit\Auditable;
use App\Modules\Account\Account;
use App\Shared\Domain\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Per-employee e-mail reminder to record the working time of the day.
 */
#[Auditable(ignore: ['lastSentOn'])]
#[ORM\Entity(repositoryClass: ReminderRepository::class)]
#[ORM\Table(name: 'time_tracking_reminder')]
class ReminderSetting extends Entity
{
    #[ORM\OneToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    public Account $account;

    #[ORM\Column]
    public bool $enabled = false;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    public \DateTimeImmutable $time;

    /** @var list<int> ISO weekdays (1 = Monday … 7 = Sunday) */
    #[ORM\Column(type: Types::JSON)]
    public array $weekdays = [1, 2, 3, 4, 5];

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $lastSentOn = null;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->time = new \DateTimeImmutable('17:00');
    }
}
