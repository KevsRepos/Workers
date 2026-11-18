<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\OrderPresetData;
use App\Modules\Pim\Product\ProductService;
use App\Shared\Exception\NotFoundException;

final readonly class OrderPresetService
{
    public function __construct(
        private Repository $presets,
        private ProductService $products,
    ) {
    }

    public function get(string $id): OrderPreset
    {
        return $this->presets->find($id) ?? throw NotFoundException::resource('Order preset', $id);
    }

    /**
     * @return list<OrderPreset>
     */
    public function list(): array
    {
        return $this->presets->findAllOrdered();
    }

    public function create(OrderPresetData $data): OrderPreset
    {
        $preset = new OrderPreset();
        $this->apply($preset, $data);
        $this->presets->add($preset);

        return $preset;
    }

    public function update(OrderPreset $preset, OrderPresetData $data): OrderPreset
    {
        $this->apply($preset, $data);

        return $preset;
    }

    public function delete(OrderPreset $preset): void
    {
        $this->presets->remove($preset);
    }

    private function apply(OrderPreset $preset, OrderPresetData $data): void
    {
        $products = $this->products->requireAll($data->productIds, 'productIds');
        $preset->name = trim($data->name);

        $existing = [];
        foreach ($preset->items as $item) {
            $existing[(string) $item->product->id] = $item;
        }
        foreach ($existing as $productId => $item) {
            if (!isset($products[$productId])) {
                $preset->items->removeElement($item);
            }
        }
        // Keep the order given by the client for new items.
        foreach ($data->productIds as $productId) {
            if (!isset($existing[$productId])) {
                $item = new OrderPresetItem();
                $item->orderPreset = $preset;
                $item->product = $products[$productId];
                $preset->items->add($item);
            }
        }
    }
}
