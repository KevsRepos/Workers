<?php

declare(strict_types=1);

namespace App\Modules\Customer\Address;

use App\Modules\Customer\Customer;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_address')]
class CustomerAddress extends Entity
{
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    public Customer $customer;

    #[ORM\Column(length: 255)]
    public string $street;

    #[ORM\Column(length: 20)]
    public string $houseNumber;

    #[ORM\Column(length: 20)]
    public string $postalCode;

    #[ORM\Column(length: 100)]
    public string $city;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $country = null;

    /** Optional free text, e.g. "Hintereingang" or a contact person on site. */
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $note = null;

    public string $singleLine {
        get => \sprintf('%s %s, %s %s', $this->street, $this->houseNumber, $this->postalCode, $this->city);
    }
}
