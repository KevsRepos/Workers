<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Modules\Pim\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class Factory
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(string $name): OrderPreset
    {
        $orderPreset = new OrderPreset();
        $orderPreset->name = $name;

        return $orderPreset;
    }

    /**
     * @param string[] $productIds
     * @return OrderPresetItem[]
     */
    public function createItems(OrderPreset $orderPreset, array $productIds): array
    {
        $entities = [];

        foreach (array_unique($productIds) as $productId) {
            $product = $this->em->getRepository(Product::class)->find($productId);
            if (!$product) {
                throw new InvalidArgumentException(sprintf('Product "%s" not found', $productId));
            }

            $item = new OrderPresetItem();
            $item->orderPreset = $orderPreset;
            $item->product = $product;
            $entities[] = $item;
        }

        return $entities;
    }
}