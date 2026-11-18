<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Modules\Pim\Product\Product;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_preset_item')]
#[ORM\UniqueConstraint(name: 'uniq_order_preset_item', columns: ['order_preset_id', 'product_id'])]
class OrderPresetItem extends Entity
{
    #[ORM\ManyToOne(targetEntity: OrderPreset::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public OrderPreset $orderPreset;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Product $product;
}
