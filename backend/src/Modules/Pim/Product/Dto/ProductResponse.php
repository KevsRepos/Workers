<?php

declare(strict_types=1);

namespace App\Modules\Pim\Product\Dto;

use App\Modules\Pim\Deposit\Dto\DepositResponse;
use App\Modules\Pim\Product\Product;

final readonly class ProductResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public ?int $salesPrice,
        public bool $sellable,
        public bool $rentable,
        public ?int $quantityInCrate,
        public ?DepositResponse $deposit,
        public bool $archived,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(
            (string) $product->id,
            $product->name,
            $product->salesPrice,
            $product->sellable,
            $product->rentable,
            $product->quantityInCrate,
            $product->deposit !== null ? DepositResponse::fromEntity($product->deposit) : null,
            $product->archived,
            $product->createdAt,
            $product->updatedAt,
        );
    }
}
