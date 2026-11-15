<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

/**
 * Delivery note statuses are intentionally unrestricted.
 *
 * A status can always be changed to any other status without enforcing
 * predefined transition rules or business constraints. Real-world workflows
 * are way too flexible and dynamic to be constrained by assumptions about which status
 * transitions should or should not be allowed under certain conditions.
 */
enum DeliveryNoteStatus: int
{
    case OPEN = 1;
    case DELIVERED = 2;
    case CANCELED = 3;
    case RETURNED = 4;
    case COMPLETED = 5;
}