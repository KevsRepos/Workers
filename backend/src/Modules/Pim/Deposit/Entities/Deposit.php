<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]

class Deposit {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $depositId;

    #[ORM\Column]
    private float $worth;

    public function getDepositId(): ?int
    {
        return $this->depositId;
    }

    public function getWorth(): float
    {
        return $this->worth;
    }

    public function setWorth(float $worth): self
    {
        $this->worth = $worth;

        return $this;
    }
}