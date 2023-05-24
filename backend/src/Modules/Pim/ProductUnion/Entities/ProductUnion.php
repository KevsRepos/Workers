<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProductUnion {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $productUnionId;

    #[ORM\Column]
    private string $name;

    public function getProductUnionId(): ?int
    {
        return $this->productUnionId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}