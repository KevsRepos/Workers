<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ReturnNoteEntry extends Entity
{
    #[ORM\Column(nullable: true)]
    public ?int $returnedTotal = null;

    #[ORM\Column(nullable: true)]
    public ?int $returnedTotalBottles = null;

    #[ORM\Column(nullable: true)]
    public ?int $returnedFull = null;

    #[ORM\Column(nullable: true)]
    public ?int $returnedFullBottles = null;
}
