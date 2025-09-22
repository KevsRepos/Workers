<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use App\Modules\Pim\Deposit\Deposit;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    public ?string $productId;

    #[ORM\Column(length: 255)]
    public string $name;

    #[ORM\Column]
    public ?int $salesPrice;

    #[ORM\ManyToOne(targetEntity: Deposit::class)]
    #[ORM\JoinColumn(name: "deposit_id", referencedColumnName: "deposit_id")]
    public ?string $depositId;
    
    // #[ORM\Column]
    // public bool $bundable;

    #[ORM\Column]
    public bool $sellable;

    #[ORM\Column]
    public bool $rentable;

    #[ORM\Column]
    public ?int $quantityInCrate;

    #[ORM\Column()]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}