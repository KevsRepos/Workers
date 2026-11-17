<?php

declare(strict_types=1);

namespace App\Modules\Customer;

use App\Modules\Customer\Address\CustomerAddress;
use App\Shared\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'customer')]
#[ORM\Index(name: 'idx_customer_surname', columns: ['surname'])]
#[ORM\Index(name: 'idx_customer_company_name', columns: ['company_name'])]
class Customer extends Entity
{
    #[ORM\Column]
    public bool $company = false;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $companyName = null;

    #[ORM\Column(length: 180, nullable: true)]
    public ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $phone = null;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: 'default_shipping_address_id', nullable: true, onDelete: 'SET NULL')]
    public ?CustomerAddress $defaultShippingAddress = null;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: 'default_billing_address_id', nullable: true, onDelete: 'SET NULL')]
    public ?CustomerAddress $defaultBillingAddress = null;

    /** @var Collection<int, CustomerAddress> */
    #[ORM\OneToMany(targetEntity: CustomerAddress::class, mappedBy: 'customer', cascade: ['persist'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    public Collection $addresses;

    /** Set when personal data was anonymised on request (GDPR Art. 17). */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $anonymizedAt = null;

    public string $displayName {
        get => $this->company
            ? (string) $this->companyName
            : trim(($this->firstName ?? '') . ' ' . ($this->surname ?? ''));
    }

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function addAddress(CustomerAddress $address): void
    {
        $address->customer = $this;
        $this->addresses->add($address);
    }

    public function ownsAddress(CustomerAddress $address): bool
    {
        return $address->customer === $this;
    }
}
