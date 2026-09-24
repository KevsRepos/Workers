<?php declare(strict_types=1);

namespace App\Modules\Customer;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Modules\Customer\Address\CustomerAddress;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Customer extends Entity
{
    #[ORM\Column]
    public bool $company;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $companyName = null;

    #[ORM\OneToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: "default_shipping_address_id", referencedColumnName: "id")]
    public ?CustomerAddress $defaultShippingAddress = null;

    #[ORM\OneToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: "default_billing_address_id", referencedColumnName: "id")]
    public ?CustomerAddress $defaultBillingAddress = null;

    #[ORM\OneToMany(mappedBy: "customer", targetEntity: CustomerAddress::class, cascade: ['persist'])]
    public Collection $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public string $displayName {
        get => $this->company ? $this->companyName : ($this->firstName . ' ' . $this->surname);
    }
}
