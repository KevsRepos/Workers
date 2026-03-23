<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: "unique_product_per_union", columns: ["product_union_id", "product_id"])]
class ProductUnionProduct extends Entity {
    #[ORM\ManyToOne(targetEntity: ProductUnion::class, inversedBy: "products")]
    #[ORM\JoinColumn(name: "product_union_id", referencedColumnName: "id")]
    #[Ignore]
    public ProductUnion $productUnion;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id")]
    public Product $product;
}