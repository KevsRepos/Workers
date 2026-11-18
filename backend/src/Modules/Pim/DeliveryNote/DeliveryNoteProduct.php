<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Audit\Auditable;
use App\Modules\Pim\Product\Product;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * A line of a delivery note.
 */
#[ORM\Entity]
#[ORM\Table(name: 'delivery_note_product')]
#[Auditable]
class DeliveryNoteProduct extends Entity
{
    #[ORM\ManyToOne(targetEntity: DeliveryNote::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public DeliveryNote $deliveryNote;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Product $product;

    /** Snapshot of the product name at the time the line was created (document integrity). */
    #[ORM\Column(length: 255)]
    public string $productName;

    #[ORM\Column(nullable: true)]
    public ?float $quantity = null;

    #[ORM\Column(options: ['default' => 0])]
    public int $position = 0;

    #[ORM\ManyToOne(targetEntity: ReturnNoteEntry::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?ReturnNoteEntry $returnNoteEntry = null;
}
