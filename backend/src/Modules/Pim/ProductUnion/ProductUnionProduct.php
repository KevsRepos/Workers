<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Modules\Pim\Product\Product;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Membership of a product in a union. A product belongs to at most one union.
 */
#[ORM\Entity]
#[ORM\Table(name: 'product_union_product')]
#[ORM\UniqueConstraint(name: 'uniq_product_union_product_product', columns: ['product_id'])]
class ProductUnionProduct extends Entity
{
    #[ORM\ManyToOne(targetEntity: ProductUnion::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ProductUnion $productUnion;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Product $product;
}
