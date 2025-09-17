<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductInBundle;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;

#[ORM\Entity]
class ProductBundle {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $productBundleId;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "product_id")]
    public int $productId;

    #[ORM\Column]
    public int $count;
}