<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;
use App\Modules\Pim\ProductUnion\ProductUnion;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ProductUnionProduct extends Entity {
    #[ORM\ManyToOne(targetEntity: ProductUnion::class)]
    #[ORM\JoinColumn(name: "product_union_id", referencedColumnName: "id")]
    public string $productUnionId;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id")]
    public string $productId;
}