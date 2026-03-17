<?php declare(strict_types=1);

namespace App\Modules\Pim\Product\Dto;

use DateTimeImmutable;

readonly final class ProductDetailResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public ?int $salesPrice,
        public bool $sellable,
        public bool $rentable,
        public ?int $quantityInCrate,
        public ?array $deposit,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt
    ){}
}
