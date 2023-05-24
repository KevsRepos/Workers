<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use App\Modules\Pim\Deposit\Entities\Deposit;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $productId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private float $salesPrice;

    #[ORM\ManyToOne(targetEntity: Deposit::class)]
    #[ORM\JoinColumn(name: "deposit_id", referencedColumnName: "deposit_id")]
    private int $depositId;
    
    #[ORM\Column]
    private bool $bundable;

    #[ORM\Column]
    private bool $sellable;

    #[ORM\Column]
    private bool $rentable;

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSalesPrice(): float
    {
        return $this->salesPrice;
    }

    public function setSalesPrice(float $salesPrice): self
    {
        $this->salesPrice = $salesPrice;

        return $this;
    }

    public function getDepositId(): int
    {
        return $this->depositId;
    }

    public function setDepositId(int $depositId): self
    {
        $this->depositId = $depositId;

        return $this;
    }

    public function getBundable(): bool
    {
        return $this->bundable;
    }

    public function setBundable(bool $bundable): self
    {
        $this->bundable = $bundable;

        return $this;
    }

    public function getSellable(): bool
    {
        return $this->sellable;
    }

    public function setSellable(bool $sellable): self
    {
        $this->sellable = $sellable;

        return $this;
    }

    public function getRentable(): bool
    {
        return $this->rentable;
    }

    public function setRentable(bool $rentable): self
    {
        $this->rentable = $rentable;

        return $this;
    }
}