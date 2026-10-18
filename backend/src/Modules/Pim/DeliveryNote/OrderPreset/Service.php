<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use App\Lib\Success;
use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\CreateOrderPresetRequestDto;
use App\Modules\Pim\DeliveryNote\OrderPreset\Dto\UpdateOrderPresetRequestDto;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use Exception;
use InvalidArgumentException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory,
    ) {}

    /** @return OrderPreset[] */
    public function list(): array
    {
        return $this->repo->findAllOrdered();
    }

    public function getById(string $id): ?OrderPreset
    {
        return $this->repo->findById($id);
    }

    public function save(CreateOrderPresetRequestDto $data): Error|Success
    {
        $orderPreset = $this->factory->create($data->name);

        try {
            foreach ($this->factory->createItems($orderPreset, $data->productIds) as $item) {
                $orderPreset->items->add($item);
            }

            $this->repo->save($orderPreset, true);
        } catch (InvalidArgumentException $e) {
            return new Error($e->getMessage(), 400);
        } catch (UniqueConstraintViolationException) {
            return new Error('OrderPreset contains duplicate products', 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success('OrderPresetCreated', ['id' => $orderPreset->id]);
    }

    public function update(string $id, UpdateOrderPresetRequestDto $data): Error|Success
    {
        $orderPreset = $this->repo->findById($id);
        if (!$orderPreset) {
            return new Error('OrderPreset not found', 404);
        }

        $requestedProductIds = array_values(array_unique($data->productIds));
        $existingItems = [];
        foreach ($orderPreset->items as $item) {
            $existingItems[$item->product->id] = $item;
        }

        $productIdsToAdd = array_diff($requestedProductIds, array_keys($existingItems));

        try {
            $itemsToAdd = $this->factory->createItems($orderPreset, $productIdsToAdd);

            foreach ($existingItems as $productId => $item) {
                if (!in_array($productId, $requestedProductIds, true)) {
                    $orderPreset->items->removeElement($item);
                }
            }

            foreach ($itemsToAdd as $item) {
                $orderPreset->items->add($item);
            }

            $orderPreset->name = $data->name;
            $this->repo->save($orderPreset, true);
        } catch (InvalidArgumentException $e) {
            return new Error($e->getMessage(), 400);
        } catch (UniqueConstraintViolationException) {
            return new Error('OrderPreset contains duplicate products', 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success('OrderPresetUpdated', ['id' => $orderPreset->id]);
    }

    public function delete(string $id): Error|Success
    {
        $orderPreset = $this->repo->findById($id);
        if (!$orderPreset) {
            return new Error('OrderPreset not found', 404);
        }

        try {
            $this->repo->remove($orderPreset, true);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success('OrderPresetDeleted', ['id' => $id]);
    }
}