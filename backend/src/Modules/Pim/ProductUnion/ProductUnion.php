<?php declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ProductUnion extends Entity {
    #[ORM\Column]
    public string $name;
}