<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ReturnNoteEntryDto
{
    /** @var string[] */
    #[Assert\NotBlank]
    public array $deliveryNoteProductIds;

    public ?int $returnedTotal = null;

    public ?int $returnedTotalBottles = null;

    public ?int $returnedFull = null;

    public ?int $returnedFullBottles = null;
}
