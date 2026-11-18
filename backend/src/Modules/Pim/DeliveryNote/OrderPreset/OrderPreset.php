<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Shared\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reusable product selection to speed up creating delivery notes.
 */
#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'order_preset')]
class OrderPreset extends Entity
{
    #[ORM\Column(length: 255)]
    public string $name;

    /** @var Collection<int, OrderPresetItem> */
    #[ORM\OneToMany(targetEntity: OrderPresetItem::class, mappedBy: 'orderPreset', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }
}
