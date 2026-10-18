<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Lib\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class OrderPreset extends Entity
{
    #[ORM\Column]
    public string $name;

    #[ORM\OneToMany(mappedBy: 'orderPreset', targetEntity: OrderPresetItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }
}