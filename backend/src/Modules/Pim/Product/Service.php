<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use Error;
use Exception;
use App\Modules\Pim\Product\Dto\CreateProductRequestDto;
use App\Modules\Pim\Product\Repository;
use App\Modules\Pim\Product\Product;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory
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
}
