<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use DateTimeImmutable;

class Factory
{
    public function create(int $singleAmount, ?int $crateAmount = null): Deposit
    {
        $deposit = new Deposit();
        $deposit->singleAmount = $singleAmount;
        $deposit->crateAmount = $crateAmount;
        
        return $deposit;
    }
}
