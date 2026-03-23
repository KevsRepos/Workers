<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use Doctrine\ORM\EntityManagerInterface;
use App\Modules\Pim\Product\Product;

class Factory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function create(string $name): ProductUnion
    {
        $productUnion = new ProductUnion();
        $productUnion->name = $name;

        return $productUnion;
    }

    /**
     * @param string[] $productIds
     * @return ProductUnionProduct[]
     */
    public function createProducts(ProductUnion $productUnion, array $productIds): array
    {
        $entities = [];

        foreach ($productIds as $productId) {
            $product = new ProductUnionProduct();
            $product->productUnion = $productUnion;
            $product->product = $this->em->getRepository(Product::class)->find($productId);
            $entities[] = $product;
        }

        return $entities;
    }
}
