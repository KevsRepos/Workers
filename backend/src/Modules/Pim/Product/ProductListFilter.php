<?php declare(strict_types=1);

namespace App\Modules\Pim\Product;

enum ProductListFilter: string
{
    case ALL = 'all';
    case WITH_DEPOSIT = 'with-deposit';
    case WITHOUT_DEPOSIT = 'without-deposit';
    case SELLABLE = 'sellable';
    case RENTABLE = 'rentable';
}
