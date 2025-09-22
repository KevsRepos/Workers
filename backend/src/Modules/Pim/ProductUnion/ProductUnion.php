<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProductUnion {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    public ?string $productUnionId;

    #[ORM\Column]
    public string $name;
}