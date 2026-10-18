<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Modules\Pim\Product\Product;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class OrderPresetItem extends Entity
{
    #[ORM\ManyToOne(targetEntity: OrderPreset::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: "order_preset_id", referencedColumnName: "id", nullable: false)]
    #[Ignore]
    public OrderPreset $orderPreset;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false)]
    public Product $product;
}