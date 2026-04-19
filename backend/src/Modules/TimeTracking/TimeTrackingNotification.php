<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Account\Account;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class TimeTrackingNotification extends Entity
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: "account_id", referencedColumnName: "id")]
    public Account $account;

    #[ORM\Column]
    public DateTimeImmutable $notificationTime;

    #[ORM\Column]
    public bool $monday = false;

    #[ORM\Column]
    public bool $tuesday = false;

    #[ORM\Column]
    public bool $wednesday = false;

    #[ORM\Column]
    public bool $thursday = false;

    #[ORM\Column]
    public bool $friday = false;
    
    #[ORM\Column]
    public bool $saturday = false;

    #[ORM\Column]
    public bool $sunday = false;
}