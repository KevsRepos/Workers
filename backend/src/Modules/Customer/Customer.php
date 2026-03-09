<?php declare(strict_types=1);

namespace App\Modules\Customer;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Modules\Customer\Address\CustomerAddress;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Customer extends Entity
{
    #[ORM\Column(length: 255)]
    public string $firstName;

    #[ORM\Column(length: 255)]
    public string $surname;

    #[ORM\OneToMany(mappedBy: "customer", targetEntity: CustomerAddress::class)]
    public Collection $addresses;
}
