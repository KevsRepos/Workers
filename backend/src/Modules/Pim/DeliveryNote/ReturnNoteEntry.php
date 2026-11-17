<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Returned quantities for one or more delivery note lines (several lines when the products
 * belong to the same product union and are counted together).
 */
#[ORM\Entity]
#[ORM\Table(name: 'return_note_entry')]
class ReturnNoteEntry extends Entity
{
    #[ORM\ManyToOne(targetEntity: DeliveryNote::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public DeliveryNote $deliveryNote;

    /** Returned crates/units in total (full and empty). */
    #[ORM\Column(nullable: true)]
    public ?int $returnedTotal = null;

    /** Returned loose bottles in total. */
    #[ORM\Column(nullable: true)]
    public ?int $returnedTotalBottles = null;

    /** Of which full (unused) crates/units. */
    #[ORM\Column(nullable: true)]
    public ?int $returnedFull = null;

    /** Of which full loose bottles. */
    #[ORM\Column(nullable: true)]
    public ?int $returnedFullBottles = null;
}
