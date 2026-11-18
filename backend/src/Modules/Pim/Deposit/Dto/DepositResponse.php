<?php

declare(strict_types=1);

namespace App\Modules\Pim\Deposit\Dto;

use App\Modules\Pim\Deposit\Deposit;

final readonly class DepositResponse
{
    public function __construct(
        public string $id,
        public ?string $label,
        public int $singleAmount,
        public ?int $crateAmount,
    ) {
    }

    public static function fromEntity(Deposit $deposit): self
    {
        return new self((string) $deposit->id, $deposit->label, $deposit->singleAmount, $deposit->crateAmount);
    }
}
