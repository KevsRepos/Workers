<?php

declare(strict_types=1);

namespace App\Modules\Pim\Product;

use App\Modules\Pim\Deposit\Deposit;
use App\Modules\Pim\Product\Dto\ProductData;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProductService
{
    public function __construct(
        private Repository $products,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(string $id): Product
    {
        return $this->products->find($id) ?? throw NotFoundException::resource('Product', $id);
    }

    /**
     * @return array{list<Product>, int}
     */
    public function search(?string $query, ProductListFilter $filter, int $offset, int $limit): array
    {
        return $this->products->search($query, $filter, $offset, $limit);
    }

    public function create(ProductData $data): Product
    {
        $product = new Product();
        $this->apply($product, $data);
        $this->products->add($product);

        return $product;
    }

    public function update(Product $product, ProductData $data): Product
    {
        $this->apply($product, $data);

        return $product;
    }

    /**
     * Loads products by id and fails with a field error if any id is unknown.
     *
     * @param list<string> $ids
     *
     * @return array<string, Product>
     */
    public function requireAll(array $ids, string $field): array
    {
        $found = $this->products->findByIds($ids);
        $missing = array_values(array_diff(array_unique($ids), array_keys($found)));
        if ($missing !== []) {
            throw BusinessRuleException::forField($field, \sprintf('Unknown product(s): %s', implode(', ', $missing)), 'unknown-product');
        }

        return $found;
    }

    private function apply(Product $product, ProductData $data): void
    {
        $product->name = trim($data->name);
        $product->salesPrice = $data->salesPrice;
        $product->sellable = $data->sellable;
        $product->rentable = $data->rentable;
        $product->quantityInCrate = $data->quantityInCrate;
        $product->archived = $data->archived;
        $product->deposit = null;

        if ($data->depositId !== null) {
            $deposit = $this->entityManager->find(Deposit::class, $data->depositId);
            if ($deposit === null) {
                throw BusinessRuleException::forField('depositId', 'Unknown deposit.', 'unknown-deposit');
            }
            $product->deposit = $deposit;
        }
    }
}
