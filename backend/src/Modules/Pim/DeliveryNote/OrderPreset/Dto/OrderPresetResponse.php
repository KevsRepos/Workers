<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset\Dto;

use App\Modules\Pim\DeliveryNote\OrderPreset\OrderPreset;
use App\Modules\Pim\DeliveryNote\OrderPreset\OrderPresetItem;
use App\Modules\Pim\Product\Dto\ProductResponse;

final readonly class OrderPresetResponse
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

    public static function fromEntity(OrderPreset $preset): self
    {
        return new self(
            (string) $preset->id,
            $preset->name,
            array_values(array_map(static fn (OrderPresetItem $item): ProductResponse => ProductResponse::fromEntity($item->product), $preset->items->toArray())),
        );
    }
}
