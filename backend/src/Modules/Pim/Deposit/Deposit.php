<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Deposit extends Entity
{
    #[ORM\Column]
    public int $singleAmount;

    #[ORM\Column]
    public ?int $crateAmount;
}