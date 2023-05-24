<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]

class ProductDeposit {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $productDepositId;

    #[ORM\ManyToOne(targetEntity: Deposit::class)]
    #[ORM\JoinColumn(name: "deposit_id", referencedColumnName: "deposit_id")]
    private float $depositId;

    public function getProductDepositId(): ?int
    {
        return $this->productDepositId;
    }

    public function getDepositId(): float
    {
        return $this->depositId;
    }

    public function setDepositId(float $depositId): self
    {
        $this->depositId = $depositId;

        return $this;
    }
}