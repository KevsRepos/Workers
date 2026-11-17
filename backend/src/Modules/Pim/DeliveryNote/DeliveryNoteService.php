<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use App\Modules\Customer\Customer;
use App\Modules\Customer\CustomerService;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteData;
use App\Modules\Pim\DeliveryNote\Dto\QuantityUpdateRequest;
use App\Modules\Pim\DeliveryNote\Dto\ReturnEntryResponse;
use App\Modules\Pim\DeliveryNote\Dto\ReturnLineGroup;
use App\Modules\Pim\DeliveryNote\Dto\ReturnNoteRequest;
use App\Modules\Pim\Deposit\Dto\DepositResponse;
use App\Modules\Pim\Product\ProductService;
use App\Modules\Pim\ProductUnion\Repository as ProductUnionRepository;
use App\Shared\Exception\BusinessRuleException;
use App\Shared\Exception\ConflictException;
use App\Shared\Exception\NotFoundException;
use App\Shared\Numbering\DocumentNumberGenerator;
use App\Shared\Security\CurrentUser;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeliveryNoteService
{
    public const string NUMBER_SEQUENCE = 'delivery_note';
    public const string NUMBER_PREFIX = 'LS';

    public function __construct(
        private Repository $notes,
        private CustomerService $customers,
        private ProductService $products,
        private ProductUnionRepository $unions,
        private DocumentNumberGenerator $numbers,
        private CurrentUser $currentUser,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(string $id): DeliveryNote
    {
        return $this->notes->findDetailed($id) ?? throw NotFoundException::resource('Delivery note', $id);
    }

    /**
     * @return array{list<DeliveryNote>, int}
     */
    public function search(?DeliveryNoteStatus $status, ?string $query, ?\DateTimeImmutable $from, ?\DateTimeImmutable $to, int $offset, int $limit, ?string $customerId = null): array
    {
        return $this->notes->search($status, $query, $from, $to, $offset, $limit, $customerId);
    }

    /**
     * @return array<int, int>
     */
    public function countByStatus(): array
    {
        return $this->notes->countByStatus();
    }

    public function create(DeliveryNoteData $data): DeliveryNote
    {
        $note = new DeliveryNote();
        $note->createdBy = $this->currentUser->accountOrNull();
        $this->applyHeader($note, $data);
        $this->applyLines($note, $data);

        $note->number = $this->numbers->next(self::NUMBER_SEQUENCE, self::NUMBER_PREFIX, (int) $note->deliveryDate->format('Y'));
        $this->notes->add($note);

        return $note;
    }

    public function update(DeliveryNote $note, DeliveryNoteData $data): DeliveryNote
    {
        $this->applyHeader($note, $data);
        $this->applyLines($note, $data);

        return $note;
    }

    public function changeStatus(DeliveryNote $note, DeliveryNoteStatus $status): DeliveryNote
    {
        $note->status = $status;

        return $note;
    }

    public function updateQuantities(DeliveryNote $note, QuantityUpdateRequest $request): DeliveryNote
    {
        foreach ($request->lines as $index => $lineData) {
            $line = $note->findLine($lineData->id)
                ?? throw BusinessRuleException::forField(\sprintf('lines[%d].id', $index), 'The line does not belong to this delivery note.', 'unknown-line');
            $line->quantity = $lineData->quantity;
        }

        return $note;
    }

    /**
     * Groups lines for the return note: products of the same union are counted together.
     *
     * @return list<ReturnLineGroup>
     */
    public function returnGroups(DeliveryNote $note): array
    {
        $lines = $note->lines->toArray();
        $unionsByProduct = $this->unions->findUnionsForProducts(array_values(array_map(static fn (DeliveryNoteProduct $l) => $l->product, $lines)));

        /** @var array<string, array{name: string, isUnion: bool, lines: list<DeliveryNoteProduct>}> $groups */
        $groups = [];
        foreach ($lines as $line) {
            $union = $unionsByProduct[(string) $line->product->id] ?? null;
            $key = $union !== null ? 'union:' . $union->id : 'line:' . $line->id;
            $groups[$key] ??= ['name' => $union->name ?? $line->productName, 'isUnion' => $union !== null, 'lines' => []];
            $groups[$key]['lines'][] = $line;
        }

        $result = [];
        foreach ($groups as $key => $group) {
            $first = $group['lines'][0];
            $result[] = new ReturnLineGroup(
                $key,
                $group['name'],
                $group['isUnion'],
                array_map(static fn (DeliveryNoteProduct $l): string => (string) $l->id, $group['lines']),
                array_map(static fn (DeliveryNoteProduct $l): string => $l->productName, $group['lines']),
                array_sum(array_map(static fn (DeliveryNoteProduct $l): float => (float) $l->quantity, $group['lines'])),
                $first->product->rentable,
                $first->product->quantityInCrate,
                $first->product->deposit !== null ? DepositResponse::fromEntity($first->product->deposit) : null,
                $first->returnNoteEntry !== null ? ReturnEntryResponse::fromEntity($first->returnNoteEntry) : null,
            );
        }

        return $result;
    }

    /**
     * Records (or corrects) returned quantities. Saving again replaces previous entries of the
     * referenced lines, so the return note can be corrected without creating duplicates.
     */
    public function saveReturnNote(DeliveryNote $note, ReturnNoteRequest $request): DeliveryNote
    {
        $seen = [];
        foreach ($request->entries as $entryIndex => $entryData) {
            $lines = [];
            foreach ($entryData->lineIds as $lineId) {
                if (isset($seen[$lineId])) {
                    throw BusinessRuleException::forField(\sprintf('entries[%d].lineIds', $entryIndex), 'A line may only be part of one return entry.', 'duplicate-line');
                }
                $seen[$lineId] = true;
                $lines[] = $note->findLine($lineId)
                    ?? throw BusinessRuleException::forField(\sprintf('entries[%d].lineIds', $entryIndex), 'The line does not belong to this delivery note.', 'unknown-line');
            }

            $existing = $lines[0]->returnNoteEntry;
            $sharedByAll = $existing !== null && array_all($lines, static fn (DeliveryNoteProduct $l): bool => $l->returnNoteEntry === $existing);
            $entry = $sharedByAll ? $existing : new ReturnNoteEntry();
            $entry->deliveryNote = $note;
            $entry->returnedTotal = $entryData->returnedTotal;
            $entry->returnedTotalBottles = $entryData->returnedTotalBottles;
            $entry->returnedFull = $entryData->returnedFull;
            $entry->returnedFullBottles = $entryData->returnedFullBottles;
            $this->entityManager->persist($entry);

            foreach ($lines as $line) {
                $previous = $line->returnNoteEntry;
                $line->returnNoteEntry = $entry;
                if ($previous !== null && $previous !== $entry) {
                    $this->removeIfOrphaned($note, $previous);
                }
            }
        }

        if ($request->markReturned) {
            $note->status = DeliveryNoteStatus::RETURNED;
        }

        return $note;
    }

    private function removeIfOrphaned(DeliveryNote $note, ReturnNoteEntry $entry): void
    {
        foreach ($note->lines as $line) {
            if ($line->returnNoteEntry === $entry) {
                return;
            }
        }
        $this->entityManager->remove($entry);
    }

    private function applyHeader(DeliveryNote $note, DeliveryNoteData $data): void
    {
        $customer = $this->customers->get($data->customerId);
        if ($customer->anonymizedAt !== null) {
            throw BusinessRuleException::forField('customerId', 'Anonymised customers cannot be used.', 'customer-anonymized');
        }

        $note->customer = $customer;
        $note->deliveryDate = new \DateTimeImmutable($data->deliveryDate);
        $note->pickupDate = $data->pickupDate !== null ? new \DateTimeImmutable($data->pickupDate) : null;
        $note->delivery = $data->delivery;
        $note->shortDescription = self::clean($data->shortDescription);
        $note->privateDescription = self::clean($data->privateDescription);
        $note->assignment = self::clean($data->assignment);
        $note->adultGuests = $data->adultGuests;
        $note->childGuests = $data->childGuests;
        $note->callBefore = $data->callBefore;
        $note->shippingAddress = $this->address($customer, $data->shippingAddressId, 'shippingAddressId');
        $note->billingAddress = $this->address($customer, $data->billingAddressId, 'billingAddressId');

        if ($note->delivery && $note->shippingAddress === null) {
            throw BusinessRuleException::forField('shippingAddressId', 'A delivery requires a shipping address.', 'shipping-address-required');
        }
    }

    /**
     * The submitted lines are authoritative: lines with id are updated, lines without id are
     * added and existing lines that are missing are removed (unless already returned).
     */
    private function applyLines(DeliveryNote $note, DeliveryNoteData $data): void
    {
        $products = $this->products->requireAll(array_map(static fn ($l): string => $l->productId, $data->lines), 'lines');

        $keep = [];
        foreach ($data->lines as $index => $lineData) {
            $product = $products[$lineData->productId];

            if ($lineData->id !== null) {
                $line = $note->findLine($lineData->id)
                    ?? throw BusinessRuleException::forField(\sprintf('lines[%d].id', $index), 'The line does not belong to this delivery note.', 'unknown-line');
                if ($line->product !== $product) {
                    if ($line->returnNoteEntry !== null) {
                        throw new ConflictException(\sprintf('The product of line "%s" cannot be changed because returns were already recorded.', $line->productName));
                    }
                    $line->product = $product;
                    $line->productName = $product->name;
                }
            } else {
                if ($product->archived) {
                    throw BusinessRuleException::forField(\sprintf('lines[%d].productId', $index), \sprintf('Product "%s" is archived.', $product->name), 'product-archived');
                }
                $line = new DeliveryNoteProduct();
                $line->deliveryNote = $note;
                $line->product = $product;
                $line->productName = $product->name;
                $note->lines->add($line);
            }

            $line->quantity = $lineData->quantity;
            $line->position = $index;
            $keep[spl_object_id($line)] = true;
        }

        foreach ($note->lines->toArray() as $line) {
            if (!isset($keep[spl_object_id($line)])) {
                if ($line->returnNoteEntry !== null) {
                    throw new ConflictException(\sprintf('Line "%s" cannot be removed because returns were already recorded.', $line->productName));
                }
                $note->lines->removeElement($line);
            }
        }
    }

    private function address(Customer $customer, ?string $addressId, string $field): ?\App\Modules\Customer\Address\CustomerAddress
    {
        return $addressId !== null ? $this->customers->ownedAddress($customer, $addressId, $field) : null;
    }

    private static function clean(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}
