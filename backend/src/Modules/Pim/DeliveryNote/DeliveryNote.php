<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Lib\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Customer\Customer;
use App\Modules\Customer\Address\CustomerAddress;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class DeliveryNote extends Entity
{
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: "customer_id", referencedColumnName: "id", nullable: false)]
    public ?Customer $customer = null;

    #[ORM\Column(type: "datetime_immutable")]
    public DateTimeImmutable $deliveryDate;

    #[ORM\Column]
    public bool $delivery;

    #[ORM\Column(enumType: DeliveryNoteStatus::class)]
    public DeliveryNoteStatus $status;

    #[ORM\Column(type: "text", nullable: true)]
    public ?string $shortDescription = null;

    #[ORM\Column(nullable: true)]
    public ?string $privateDescription = null;

    #[ORM\Column(nullable: true)]
    public ?string $assignment = null;

    #[ORM\OneToMany(mappedBy: "deliveryNote", targetEntity: DeliveryNoteProduct::class, cascade: ["persist", "remove"])]
    public Collection $deliveryNoteProducts;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: "shipping_address_id", referencedColumnName: "id", nullable: true)]
    public ?CustomerAddress $shippingAddress = null;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(name: "billing_address_id", referencedColumnName: "id", nullable: true)]
    public ?CustomerAddress $billingAddress = null;

    #[ORM\Column(nullable: true)]
    public ?int $adultGuests = null;

    #[ORM\Column(nullable: true)]
    public ?int $childGuests = null;
}