<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

enum DeliveryNoteStatus: int
{
    case OPEN = 1;
    case DELIVERED = 2;
    case CANCELED = 3;
    case RETURNED = 4;
    case COMPLETED = 5;
}