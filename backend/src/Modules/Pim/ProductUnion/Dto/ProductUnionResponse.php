<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion\Dto;

use App\Modules\Pim\Product\Dto\ProductResponse;
use App\Modules\Pim\ProductUnion\ProductUnion;
use App\Modules\Pim\ProductUnion\ProductUnionProduct;

final readonly class ProductUnionResponse
{
    /**
     * @param list<ProductResponse> $products
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $products,
    ) {
    }

    public static function fromEntity(ProductUnion $union): self
    {
        $products = array_map(static fn (ProductUnionProduct $m): ProductResponse => ProductResponse::fromEntity($m->product), $union->products->toArray());
        usort($products, static fn (ProductResponse $a, ProductResponse $b): int => strcasecmp($a->name, $b->name));

        return new self((string) $union->id, $union->name, $products);
    }
}
