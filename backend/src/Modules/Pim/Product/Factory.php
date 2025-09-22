<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use DateTimeImmutable;

class Factory {
    public function create(
        string $name,
        ?int $salesPrice,
        ?string $depositId,
        // bool $bundable = false,
        bool $sellable = false,
        bool $rentable = false,
        ?int $quantityInCrate = null
    ): Product {
        $product = new Product();
        $product->name = $name;
        $product->salesPrice = $salesPrice;
        $product->depositId = $depositId;
        // $product->bundable = $bundable;
        $product->sellable = $sellable;
        $product->rentable = $rentable;
        $product->quantityInCrate = $quantityInCrate;
        $product->createdAt = new DateTimeImmutable();
        
        return $product;
    }
}
