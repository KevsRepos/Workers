<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Modules\Pim\Deposit\Deposit;

class Factory {
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function create(
        string $name,
        ?int $salesPrice,
        ?string $depositId,
        // bool $bundable = false,
        bool $sellable = false,
        bool $rentable = false,
        ?int $quantityInCrate = null
    ): Product {
        $product = new Product();
        $product->name = $name;
        $product->salesPrice = $salesPrice;

        // Retrieve Deposit entity if depositId is provided
        $product->deposit = $depositId
            ? $this->em->getRepository(Deposit::class)->find($depositId)
            : null;

        // $product->bundable = $bundable;
        $product->sellable = $sellable;
        $product->rentable = $rentable;
        $product->quantityInCrate = $quantityInCrate;
        
        return $product;
    }
}