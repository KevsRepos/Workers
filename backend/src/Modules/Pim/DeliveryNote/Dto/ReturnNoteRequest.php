<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ReturnNoteRequest
{
    /** @var list<ReturnEntryData> */
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $entries = [];

    /** Set the delivery note to "returned" after saving. */
    public bool $markReturned = true;
}
