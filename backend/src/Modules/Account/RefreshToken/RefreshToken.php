<?php declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Account\Account;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class RefreshToken extends Entity
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: "account_id", referencedColumnName: "id", nullable: false)]
    public ?Account $account = null;

    #[ORM\Column]
    public string $token;

    #[ORM\Column(type: "datetime_immutable")]
    public DateTimeImmutable $expires;
}