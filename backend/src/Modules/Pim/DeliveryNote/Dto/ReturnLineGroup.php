<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\Deposit\Dto\DepositResponse;

/**
 * Lines that are counted together on the return note (same product union) or a single line.
 */
final readonly class ReturnLineGroup
{
    /**
     * @param list<string> $lineIds
     * @param list<string> $productNames
     */
    public function __construct(
        public string $key,
        public string $name,
        public bool $isUnion,
        public array $lineIds,
        public array $productNames,
        public float $quantity,
        public bool $rentable,
        public ?int $quantityInCrate,
        public ?DepositResponse $deposit,
        public ?ReturnEntryResponse $returnEntry,
    ) {
    }
}
