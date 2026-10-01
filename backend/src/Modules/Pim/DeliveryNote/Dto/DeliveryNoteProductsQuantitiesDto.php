<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DeliveryNoteProductsQuantitiesDto
{
    #[Assert\All(new Assert\Type(type: DeliveryNoteProductDto::class))]
    #[Assert\Valid]
    /** @var DeliveryNoteProductDto[] */
    public array $products;
}