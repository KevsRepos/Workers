<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\DeliveryNoteProduct;
use App\Modules\Pim\Deposit\Dto\DepositResponse;

final readonly class DeliveryNoteLineResponse
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $productName,
        public ?float $quantity,
        public int $position,
        public bool $rentable,
        public bool $sellable,
        public ?int $quantityInCrate,
        public ?int $salesPrice,
        public ?DepositResponse $deposit,
        public ?ReturnEntryResponse $returnEntry,
    ) {
    }

    public static function fromEntity(DeliveryNoteProduct $line): self
    {
        $product = $line->product;

        return new self(
            (string) $line->id,
            (string) $product->id,
            $line->productName,
            $line->quantity,
            $line->position,
            $product->rentable,
            $product->sellable,
            $product->quantityInCrate,
            $product->salesPrice,
            $product->deposit !== null ? DepositResponse::fromEntity($product->deposit) : null,
            $line->returnNoteEntry !== null ? ReturnEntryResponse::fromEntity($line->returnNoteEntry) : null,
        );
    }
}
