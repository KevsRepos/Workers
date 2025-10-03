<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use App\Lib\Entity;
use App\Modules\Pim\Deposit\Deposit;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product extends Entity {
    #[ORM\Column(length: 255)]
    public string $name;

    #[ORM\Column(nullable: true)]
    public ?int $salesPrice;

    #[ORM\ManyToOne(targetEntity: Deposit::class)]
    #[ORM\JoinColumn(name: "deposit_id", referencedColumnName: "id")]
    public ?Deposit $deposit;

    // #[ORM\Column]
    // public bool $bundable;

    #[ORM\Column]
    public bool $sellable;

    #[ORM\Column]
    public bool $rentable;

    #[ORM\Column(nullable: true)]
    public ?int $quantityInCrate;
}