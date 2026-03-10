<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Modules\Customer\Customer;
use App\Modules\Pim\Product\Product;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteProductDto;

class Factory {
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function createDeliveryNote(
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

    /**
     * @param DeliveryNoteProductDto[] $deliveryNoteProducts
     * @return DeliveryNoteProduct[]
     */
    public function createDeliveryNoteProducts(
        string $deliveryNoteId,
        array $deliveryNoteProducts,
    ): array {
        $deliveryNote = $this->em->getRepository(DeliveryNote::class)->find($deliveryNoteId);
        $entities = [];

        foreach ($deliveryNoteProducts as $productDto) {
            $deliveryNoteProduct = new DeliveryNoteProduct();
            $deliveryNoteProduct->deliveryNote = $deliveryNote;
            $deliveryNoteProduct->product = $this->em->getRepository(Product::class)->find($productDto->productId);
            $deliveryNoteProduct->quantity = $productDto->quantity;
            $entities[] = $deliveryNoteProduct;
        }

        return $entities;
    }
}
