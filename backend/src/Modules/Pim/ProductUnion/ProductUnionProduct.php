<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;
use App\Modules\Pim\ProductUnion\ProductUnion;

#[ORM\Entity]
class ProductUnionProduct {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    public ?string $productUnionProductId;

    #[ORM\ManyToOne(targetEntity: ProductUnion::class)]
    #[ORM\JoinColumn(name: "product_union_id", referencedColumnName: "product_union_id")]
    public string $productUnionId;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "product_id")]
    public string $productId;
}