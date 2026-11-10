<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use Error;
use Exception;
use App\Modules\Pim\DeliveryNote\Dto\CreateReturnNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\CreateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\UpdateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\DeliveryNoteProductsQuantitiesDto;
use App\Modules\Pim\DeliveryNote\Repository;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Modules\Customer\Service as CustomerService;
use App\Modules\Customer\Address\Service as CustomerAddressService;
use App\Modules\Pim\ProductUnion\ProductUnionProduct;
use Doctrine\ORM\EntityManagerInterface;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private CustomerService $customerService,
        private CustomerAddressService $customerAddressService,
        private EntityManagerInterface $em,
    ) {}

    public function getById(string $id): ?DeliveryNote
    {
        return $this->repo->findById($id);
    }

    /**
     * @return DeliveryNote[]
     */
    public function listByStatus(int $status): array
    {
        return $this->repo->findByStatus(DeliveryNoteStatus::from($status));
    }

    public function save(CreateDeliveryNoteRequestDto $data): Error|Success
    {
        $deliveryNote = $this->factory->createNewDeliveryNote(
            $data->customerId,
            $data->deliveryDate,
            $data->pickupDate,
            $data->delivery,
            $data->shortDescription,
            $data->privateDescription,
            $data->assignment,
            $data->shippingAddressId,
            $data->billingAddressId,
            $data->adultGuests,
            $data->childGuests,
            $data->callBefore,
        );

        try {
            $deliveryNoteId = $this->repo->saveDeliveryNote($deliveryNote, true);

            $deliveryNoteProducts = $this->factory->createDeliveryNoteProducts(
                $deliveryNote,
                $data->deliveryNoteProducts,
            );
            $this->repo->saveDeliveryNoteProducts($deliveryNoteProducts, true);

            return new Success("DeliveryNoteCreated", ['id' => $deliveryNoteId]);
        } catch(\InvalidArgumentException $e) {
            return new Error($e->getMessage(), 400);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception $e) {
            return new Error($e->getMessage(), 500);
        }
    }

    public function update(string $id, UpdateDeliveryNoteRequestDto $data): Error|Success
    {
        $deliveryNote = $this->repo->findById($id);

        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $existingProductIds = array_values(array_filter(array_map(
            static fn($product): ?string => $product->id,
            $data->deliveryNoteProducts,
        ), static fn(?string $id): bool => $id !== null));
        
        $productIds = array_merge($existingProductIds, $data->removedProductIds);

        if (
            count($productIds) !== count(array_unique($productIds))
            || count($this->repo->findDeliveryNoteProducts($deliveryNote, $productIds)) !== count($productIds)
        ) {
            return new Error("Invalid delivery note product IDs", 400);
        }

        if ($data->customerId) {
            $deliveryNote->customer = $this->customerService->findById($data->customerId);
        }

        if ($data->deliveryDate) {
            $deliveryNote->deliveryDate = new \DateTimeImmutable($data->deliveryDate);
        }

        if ($data->pickupDate) {
            $deliveryNote->pickupDate = new \DateTimeImmutable($data->pickupDate);
        }

        if ($data->delivery !== null) {
            $deliveryNote->delivery = $data->delivery;
        }

        if ($data->status !== null) {
            $deliveryNote->status = $data->status;
        }

        $deliveryNote->shortDescription = $data->shortDescription;
        $deliveryNote->privateDescription = $data->privateDescription;
        $deliveryNote->assignment = $data->assignment;
        $deliveryNote->adultGuests = $data->adultGuests;
        $deliveryNote->childGuests = $data->childGuests;
        $deliveryNote->callBefore = $data->callBefore;

        if ($data->shippingAddressId) {
            $deliveryNote->shippingAddress = $this->customerAddressService->getAddress($data->shippingAddressId);
        } elseif($data->shippingAddressId === null) {
            $deliveryNote->shippingAddress = null;
        }

        if ($data->billingAddressId) {
            $deliveryNote->billingAddress = $this->customerAddressService->getAddress($data->billingAddressId);
        } elseif($data->billingAddressId === null) {
            $deliveryNote->billingAddress = null;
        }

        if ($data->deliveryNoteProducts) {
            // $deliveryNote->deliveryNoteProducts = $data->deliveryNoteProducts;
            $deliveryNote->deliveryNoteProducts = $this->factory->createDeliveryNoteProducts(
                $deliveryNote,
                $data->deliveryNoteProducts,
            );
            // $this->repo->saveDeliveryNoteProducts($deliveryNoteProducts, true);
        }

        if ($data->removedProductIds) {
            if (!$this->repo->removeDeliveryNoteProducts($deliveryNote, $data->removedProductIds)) {
                return new Error("Invalid delivery note product IDs", 400);
            }
        }

        try {
            $this->repo->saveDeliveryNote($deliveryNote, true);
            
            return new Success("DeliveryNoteUpdated", ['id' => $id]);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }
    }

    public function updateStatus(string $id, int $status): Error|Success
    {
        $deliveryNote = $this->repo->findById($id);
        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $statusEnum = DeliveryNoteStatus::tryFrom($status);
        if (!$statusEnum) {
            return new Error("Invalid status", 400);
        }

        $deliveryNote->status = $statusEnum;

        try {
            $this->repo->saveDeliveryNote($deliveryNote, true);
            return new Success("StatusUpdated");
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }
    }

    public function createReturnNote(string $deliveryNoteId, CreateReturnNoteRequestDto $data): Error|Success
    {
        if ($data->deliveryNoteId !== $deliveryNoteId) {
            return new Error("Delivery note ID does not match request path", 400);
        }

        $deliveryNote = $this->repo->findById($deliveryNoteId);

        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $productIds = [];
        foreach ($data->returnNoteEntries as $entry) {
            $productIds = array_merge($productIds, $entry->deliveryNoteProductIds);
        }
        if (
            count($productIds) !== count(array_unique($productIds))
            || count($this->repo->findDeliveryNoteProducts($deliveryNote, $productIds)) !== count($productIds)
        ) {
            return new Error("Invalid delivery note product IDs", 400);
        }

        try {
            $entries = $this->factory->createReturnNoteEntries($deliveryNote, $data->returnNoteEntries);
        } catch (\InvalidArgumentException $e) {
            return new Error($e->getMessage(), 400);
        }

        $deliveryNote->status = DeliveryNoteStatus::RETURNED;

        try {
            $this->repo->saveReturnNoteEntries($entries, false);
            $this->repo->saveDeliveryNote($deliveryNote, true);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("ReturnNoteCreated", ['id' => $data->deliveryNoteId]);
    }

    public function getReturnUnions(string $deliveryNoteId): Error|array
    {
        $deliveryNote = $this->repo->findById($deliveryNoteId);

        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $products = [];
        foreach ($deliveryNote->deliveryNoteProducts as $dnp) {
            $products[] = $dnp->product;
        }

        $productUnionMap = [];

        if (!empty($products)) {
            $unionProducts = $this->em->getRepository(ProductUnionProduct::class)
                ->findBy(['product' => $products]);

            foreach ($unionProducts as $pup) {
                $productUnionMap[$pup->product->id] = $pup->productUnion;
            }
        }

        $unionEntries = [];
        $standalone = [];

        foreach ($deliveryNote->deliveryNoteProducts as $dnp) {
            $productId = $dnp->product->id;

            if (isset($productUnionMap[$productId])) {
                $union = $productUnionMap[$productId];
                if (!isset($unionEntries[$union->id])) {
                    $unionEntries[$union->id] = [
                        'name' => $union->name,
                        'isUnion' => true,
                        'quantity' => 0,
                        'rentable' => $dnp->product->rentable,
                        'quantityInCrate' => $dnp->product->quantityInCrate,
                        'deposit' => $dnp->product->deposit,
                        'deliveryNoteProductIds' => [],
                        'returnNoteEntry' => $dnp->returnNoteEntry,
                    ];
                }
                $unionEntries[$union->id]['quantity'] += $dnp->quantity;
                $unionEntries[$union->id]['deliveryNoteProductIds'][] = $dnp->id;
            } else {
                $standalone[] = [
                    'name' => $dnp->product->name,
                    'isUnion' => false,
                    'quantity' => $dnp->quantity,
                    'rentable' => $dnp->product->rentable,
                    'quantityInCrate' => $dnp->product->quantityInCrate,
                    'deposit' => $dnp->product->deposit,
                    'deliveryNoteProductIds' => [$dnp->id],
                    'returnNoteEntry' => $dnp->returnNoteEntry,
                ];
            }
        }

        return [
            'deliveryNote' => $deliveryNote,
            'returnUnions' => array_merge(array_values($unionEntries), $standalone),
        ];
    }

    function updateProductQuantities(string $deliveryNoteId, DeliveryNoteProductsQuantitiesDto $dto): Error|Success
    {
        $deliveryNote = $this->repo->findById($deliveryNoteId);

        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $productIds = array_map(static fn($product): ?string => $product->id, $dto->products);
        if (
            in_array(null, $productIds, true)
            || count($productIds) !== count(array_unique($productIds))
            || count($this->repo->findDeliveryNoteProducts($deliveryNote, $productIds)) !== count($productIds)
        ) {
            return new Error("Invalid delivery note product IDs", 400);
        }

        foreach ($dto->products as $dnp) {
            $deliveryNoteProduct = $this->em->getRepository(DeliveryNoteProduct::class)->findOneBy([
                'id' => $dnp->id,
                'deliveryNote' => $deliveryNote,
            ]);
            $deliveryNoteProduct->quantity = $dnp->quantity;
            $this->em->persist($deliveryNoteProduct);
        }

        $this->em->flush();

        return new Success("ProductQuantitiesUpdated", ['id' => $deliveryNoteId]);
    }
}
