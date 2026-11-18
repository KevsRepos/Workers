<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Modules\Pim\Product\ProductService;
use App\Modules\Pim\ProductUnion\Dto\ProductUnionData;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\NotFoundException;

final readonly class ProductUnionService
{
    public function __construct(
        private Repository $unions,
        private ProductService $products,
    ) {
    }

    public function get(string $id): ProductUnion
    {
        return $this->unions->find($id) ?? throw NotFoundException::resource('Product union', $id);
    }

    /**
     * @return list<ProductUnion>
     */
    public function list(): array
    {
        return $this->unions->findAllWithProducts();
    }

    public function create(ProductUnionData $data): ProductUnion
    {
        $union = new ProductUnion();
        $this->apply($union, $data);
        $this->unions->add($union);

        return $union;
    }

    public function update(ProductUnion $union, ProductUnionData $data): ProductUnion
    {
        $this->apply($union, $data);

        return $union;
    }

    public function delete(ProductUnion $union): void
    {
        $this->unions->remove($union);
    }

    private function apply(ProductUnion $union, ProductUnionData $data): void
    {
        $products = $this->products->requireAll($data->productIds, 'productIds');

        $conflicts = [];
        foreach ($this->unions->findUnionsForProducts(array_values($products)) as $productId => $otherUnion) {
            if ($otherUnion !== $union) {
                $conflicts[] = \sprintf('%s (in "%s")', $products[$productId]->name, $otherUnion->name);
            }
        }
        if ($conflicts !== []) {
            throw BusinessRuleException::forField('productIds', 'Products already belong to another union: ' . implode(', ', $conflicts), 'product-in-other-union');
        }

        $union->name = trim($data->name);

        $existing = [];
        foreach ($union->products as $membership) {
            $existing[(string) $membership->product->id] = $membership;
        }
        foreach ($existing as $productId => $membership) {
            if (!isset($products[$productId])) {
                $union->products->removeElement($membership);
            }
        }
        foreach ($products as $productId => $product) {
            if (!isset($existing[$productId])) {
                $membership = new ProductUnionProduct();
                $membership->productUnion = $union;
                $membership->product = $product;
                $union->products->add($membership);
            }
        }
    }
}
