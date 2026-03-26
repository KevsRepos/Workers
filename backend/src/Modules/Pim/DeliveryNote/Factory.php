<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Modules\Customer\Customer;
use App\Modules\Pim\Product\Product;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteProductDto;
use Doctrine\Common\Collections\ArrayCollection;

class Factory {
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function createNewDeliveryNote(
        string $customerId,
        string $deliveryDate,
        bool $delivery,
    ): DeliveryNote {
        $deliveryNote = new DeliveryNote();

        $deliveryNote->customer = $this->em->getRepository(Customer::class)->find($customerId);
        $deliveryNote->deliveryDate = new DateTimeImmutable($deliveryDate);
        $deliveryNote->delivery = $delivery;
        $deliveryNote->status = DeliveryNoteStatus::OPEN;

        return $deliveryNote;
    }

    public function createDeliveryNote(
        string $id,
        ?string $customerId,
        ?string $deliveryDate,
        ?bool $delivery,
        ?DeliveryNoteStatus $status,
    ): DeliveryNote {
        $deliveryNote = new DeliveryNote();

        $deliveryNote->id = $id;

        if ($customerId !== null) {
            $deliveryNote->customer = $this->em->getRepository(Customer::class)->find($customerId);
        }
        if ($deliveryDate !== null) {
            $deliveryNote->deliveryDate = new DateTimeImmutable($deliveryDate);
        }
        if ($delivery !== null) {
            $deliveryNote->delivery = $delivery;
        }
        if ($status !== null) {
            $deliveryNote->status = $status;
        }

        return $deliveryNote;
    }

    /**
     * @param DeliveryNoteProductDto[] $deliveryNoteProducts
     */
    public function createDeliveryNoteProducts(
        string $deliveryNoteId,
        array $deliveryNoteProducts,
    ): ArrayCollection {
        $deliveryNote = $this->em->getRepository(DeliveryNote::class)->find($deliveryNoteId);
        $entities = new ArrayCollection();

        foreach ($deliveryNoteProducts as $productDto) {
            $deliveryNoteProduct = new DeliveryNoteProduct();

            if ($productDto->id) {
                $deliveryNoteProduct = $this->em->getRepository(DeliveryNoteProduct::class)->find($productDto->id);
                if (!$deliveryNoteProduct) {
                    continue;
                }
            }

            $deliveryNoteProduct->deliveryNote = $deliveryNote;
            $deliveryNoteProduct->product = $this->em->getRepository(Product::class)->find($productDto->productId);
            $deliveryNoteProduct->quantity = $productDto->quantity;

            $entities->add($deliveryNoteProduct);
            // $entities[] = $deliveryNoteProduct;
        }

        return $entities;
    }

    /**
     * @param Dto\ReturnNoteEntryDto[] $returnNoteEntryDtos
     */
    public function createReturnNoteEntries(array $returnNoteEntryDtos): ArrayCollection
    {
        $entries = new ArrayCollection();

        foreach ($returnNoteEntryDtos as $dto) {
            $entry = new ReturnNoteEntry();
            $entry->returnedTotal = $dto->returnedTotal;
            $entry->returnedTotalBottles = $dto->returnedTotalBottles;
            $entry->returnedFull = $dto->returnedFull;
            $entry->returnedFullBottles = $dto->returnedFullBottles;

            foreach ($dto->deliveryNoteProductIds as $dnpId) {
                $dnp = $this->em->getRepository(DeliveryNoteProduct::class)->find($dnpId);
                if ($dnp) {
                    $dnp->returnNoteEntry = $entry;
                }
            }

            $entries->add($entry);
        }

        return $entries;
    }
}
