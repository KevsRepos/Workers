<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use App\Modules\Pim\DeliveryNote\ReturnNoteEntry;

final readonly class ReturnEntryResponse
{
    public function __construct(
        public string $id,
        public ?int $returnedTotal,
        public ?int $returnedTotalBottles,
        public ?int $returnedFull,
        public ?int $returnedFullBottles,
    ) {
    }

    public static function fromEntity(ReturnNoteEntry $entry): self
    {
        return new self((string) $entry->id, $entry->returnedTotal, $entry->returnedTotalBottles, $entry->returnedFull, $entry->returnedFullBottles);
    }
}
