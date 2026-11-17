<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\DeliveryNoteStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class StatusChangeRequest
{
    #[Assert\NotNull]
    public ?DeliveryNoteStatus $status = null;
}
