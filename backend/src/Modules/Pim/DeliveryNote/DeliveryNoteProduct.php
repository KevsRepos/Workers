<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class DeliveryNoteProduct extends Entity
{
    #[ORM\ManyToOne(targetEntity: DeliveryNote::class)]
    #[ORM\JoinColumn(name: "delivery_note_id", referencedColumnName: "id", nullable: false)]
    #[Ignore]
    public DeliveryNote $deliveryNote;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false)]
    public Product $product;

    #[ORM\Column]
    public int $quantity;
}