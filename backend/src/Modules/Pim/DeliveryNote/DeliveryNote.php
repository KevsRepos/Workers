<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Modules\Account\Account;
use App\Modules\Customer\Address\CustomerAddress;
use App\Modules\Customer\Customer;
use App\Shared\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'delivery_note')]
#[ORM\Index(name: 'idx_delivery_note_status_date', columns: ['status', 'delivery_date'])]
#[ORM\Index(name: 'idx_delivery_note_delivery_date', columns: ['delivery_date'])]
class DeliveryNote extends Entity
{
    /** Human readable, gap-free document number per year, e.g. "LS-2026-00042". */
    #[ORM\Column(length: 32, unique: true)]
    public string $number;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false)]
    public Customer $customer;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public \DateTimeImmutable $deliveryDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $pickupDate = null;

    /** true: delivered by us, false: collected by the customer. */
    #[ORM\Column]
    public bool $delivery = false;

    #[ORM\Column(enumType: DeliveryNoteStatus::class)]
    public DeliveryNoteStatus $status = DeliveryNoteStatus::OPEN;

    /** Visible on the customer copy. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $shortDescription = null;

    /** Internal note, never printed for the customer. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $privateDescription = null;

    /** Who is responsible for delivery / pickup. */
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $assignment = null;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ?CustomerAddress $shippingAddress = null;

    #[ORM\ManyToOne(targetEntity: CustomerAddress::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ?CustomerAddress $billingAddress = null;

    #[ORM\Column(nullable: true)]
    public ?int $adultGuests = null;

    #[ORM\Column(nullable: true)]
    public ?int $childGuests = null;

    #[ORM\Column]
    public bool $callBefore = false;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Account $createdBy = null;

    /** @var Collection<int, DeliveryNoteProduct> */
    #[ORM\OneToMany(targetEntity: DeliveryNoteProduct::class, mappedBy: 'deliveryNote', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    public Collection $lines;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
    }

    public function findLine(string $lineId): ?DeliveryNoteProduct
    {
        foreach ($this->lines as $line) {
            if ($line->id === $lineId) {
                return $line;
            }
        }

        return null;
    }
}
