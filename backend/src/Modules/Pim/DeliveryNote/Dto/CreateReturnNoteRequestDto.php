<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateReturnNoteRequestDto
{
    #[Assert\NotBlank]
    public string $deliveryNoteId;

    /** @var ReturnNoteEntryDto[] */
    #[Assert\NotBlank]
    #[Assert\Valid]
    public array $returnNoteEntries;
}