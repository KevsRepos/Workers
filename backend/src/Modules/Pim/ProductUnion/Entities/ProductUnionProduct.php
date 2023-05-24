<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion\Entities;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;

#[ORM\Entity]
class ProductUnionProduct {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $productUnionId;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "product_id")]
    private int $productId;

    public function getProductUnionId(): ?int
    {
        return $this->productUnionId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }
}