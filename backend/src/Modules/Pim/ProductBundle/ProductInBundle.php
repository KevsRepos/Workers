<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductInBundle;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Pim\Product\Product;

#[ORM\Entity]
class ProductInBundle {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $productBundleId;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "product_id")]
    private int $productId;

    #[ORM\Column]
    private int $count;

    public function getProductBundleId(): ?int
    {
        return $this->productBundleId;
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

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}