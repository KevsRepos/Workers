<?php

declare(strict_types=1);

namespace App\Modules\Pim\Product;

use App\Modules\Pim\Deposit\Deposit;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'product')]
#[ORM\Index(name: 'idx_product_name', columns: ['name'])]
class Product extends Entity
{
    #[ORM\Column(length: 255)]
    public string $name;

    /** Sales price in euro cents. */
    #[ORM\Column(nullable: true)]
    public ?int $salesPrice = null;

    #[ORM\ManyToOne(targetEntity: Deposit::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Deposit $deposit = null;

    #[ORM\Column]
    public bool $sellable = false;

    #[ORM\Column]
    public bool $rentable = false;

    /** Number of units per crate, if sold in crates. */
    #[ORM\Column(nullable: true)]
    public ?int $quantityInCrate = null;

    /** Archived products are hidden from selection but kept for existing documents. */
    #[ORM\Column(options: ['default' => false])]
    public bool $archived = false;
}
