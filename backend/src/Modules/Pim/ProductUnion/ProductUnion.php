<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Shared\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Groups interchangeable products (e.g. different beers in the same crate type) that are
 * counted together on return notes.
 */
#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'product_union')]
class ProductUnion extends Entity
{
    #[ORM\Column(length: 255)]
    public string $name;

    /** @var Collection<int, ProductUnionProduct> */
    #[ORM\OneToMany(targetEntity: ProductUnionProduct::class, mappedBy: 'productUnion', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
}
