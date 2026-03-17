<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use Error;
use Exception;
use App\Modules\Pim\Product\Dto\CreateProductRequestDto;
use App\Modules\Pim\Product\Dto\UpdateProductRequestDto;
use App\Modules\Pim\Product\Dto\ProductResponseDto;
use App\Modules\Pim\Product\Repository;
use App\Modules\Pim\Product\Product;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Doctrine\ORM\EntityManagerInterface;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private EntityManagerInterface $em
    ) {}

    public function save(CreateProductRequestDto $data): Error|Success
    {
        $product = $this->factory->create(
            $data->name,
            $data->salesPrice,
            $data->depositId,
            // $data->bundable,
            $data->sellable,
            $data->rentable,
            $data->quantityInCrate
        );

        try {
            $this->repo->save($product, true);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("ProductCreated");
    }

    public function search(string $query): array
    {
        return $this->repo->search($query);
    }

    public function getById(string $id): ?Product
    {
        return $this->repo->findById($id);
    }

    public function update(string $id, UpdateProductRequestDto $data): Error|Success
    {
        $product = $this->repo->findById($id);
        if (!$product) {
            return new Error("ProductNotFound", 404);
        }

        $product->name = $data->name;
        $product->salesPrice = $data->salesPrice;
        $product->sellable = $data->sellable;
        $product->rentable = $data->rentable;
        $product->quantityInCrate = $data->quantityInCrate;
        $product->deposit = $data->depositId
            ? $this->em->getRepository(\App\Modules\Pim\Deposit\Deposit::class)->find($data->depositId)
            : null;

        try {
            $this->repo->save($product, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("ProductUpdated");
    }

    public function list(int $page = 1, ProductListFilter $filter = ProductListFilter::ALL): ProductResponseDto
    {
        $limit = 20;
        
        $limit = max(1, min(100, $limit));
        $page = max(1, $page);

        $data = $this->repo->listPaginated($page, $limit, $filter);
        $total = $this->repo->countAll($filter);

        $dto = new ProductResponseDto();
        $dto->data = $data;
        $dto->page = $page;
        $dto->limit = $limit;
        $dto->total = $total;

        return $dto;
    }
}
