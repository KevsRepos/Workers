<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

/**
 * Delivery note statuses are intentionally unrestricted.
 *
 * A status can always be changed to any other status without enforcing predefined transition
 * rules: real-world workflows are too flexible to be constrained by assumptions about which
 * transitions are allowed. Every change is recorded in the audit trail instead.
 */
enum DeliveryNoteStatus: int
{
    case OPEN = 1;
    case DELIVERED = 2;
    case CANCELED = 3;
    case RETURNED = 4;
    case COMPLETED = 5;

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Offen',
            self::DELIVERED => 'Geliefert',
            self::CANCELED => 'Storniert',
            self::RETURNED => 'Zurückgegeben',
            self::COMPLETED => 'Abgeschlossen',
        };
    }
}
