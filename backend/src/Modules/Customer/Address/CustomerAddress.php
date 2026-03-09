<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use App\Lib\Entity;
use App\Modules\Customer\Customer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class CustomerAddress extends Entity
{
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: false)]
    #[Ignore]
    public ?Customer $customer = null;

    #[ORM\Column(length: 255)]
    public string $street;

    #[ORM\Column(length: 50)]
    public string $houseNumber;

    #[ORM\Column(length: 20)]
    public string $postalCode;

    #[ORM\Column(length: 100)]
    public string $city;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $country = null;

    #[ORM\Column]
    public bool $isPrimary = false;
}
