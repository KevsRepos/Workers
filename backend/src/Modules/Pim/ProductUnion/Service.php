<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use Error;
use Exception;
use App\Modules\Pim\ProductUnion\Dto\CreateProductUnionRequestDto;
use App\Modules\Pim\ProductUnion\Dto\UpdateProductUnionRequestDto;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory
    ) {}

    public function save(CreateProductUnionRequestDto $data): Error|Success
    {
        $productUnion = $this->factory->create($data->name);

        try {
            $this->repo->save($productUnion, true);

            $products = $this->factory->createProducts($productUnion, $data->productIds);
            $this->repo->saveProducts($products);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("ProductUnionCreated", ['id' => $productUnion->id]);
    }

    public function list(): array
    {
        return $this->repo->listAll();
    }

    public function getById(string $id): ?ProductUnion
    {
        return $this->repo->findById($id);
    }

    public function update(string $id, UpdateProductUnionRequestDto $data): Error|Success
    {
        $productUnion = $this->repo->findById($id);
        if (!$productUnion) {
            return new Error("ProductUnionNotFound", 404);
        }

        $productUnion->name = $data->name;

        $existingProducts = $productUnion->products->toArray();
        $existingProductIds = array_map(fn(ProductUnionProduct $pup) => $pup->product->id, $existingProducts);

        $toAdd = array_diff($data->productIds, $existingProductIds);
        $toRemoveIds = array_map(
            fn(ProductUnionProduct $pup) => $pup->id,
            array_filter($existingProducts, fn(ProductUnionProduct $pup) => !in_array($pup->product->id, $data->productIds))
        );

        try {
            if (!empty($toRemoveIds)) {
                $this->repo->removeProducts($toRemoveIds);
            }

            if (!empty($toAdd)) {
                $products = $this->factory->createProducts($productUnion, $toAdd);
                $this->repo->saveProducts($products);
            }

            $this->repo->save($productUnion, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("ProductUnionUpdated");
    }
}
