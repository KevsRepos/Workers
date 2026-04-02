<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use Error;
use Exception;
use App\Modules\Pim\DeliveryNote\Dto\CreateReturnNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\CreateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\UpdateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Repository;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Modules\Customer\Service as CustomerService;
use App\Modules\Pim\ProductUnion\ProductUnionProduct;
use Doctrine\ORM\EntityManagerInterface;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private CustomerService $customerService,
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
            $data->delivery,
            $data->shortDescription,
            $data->assignment,
        );

        try {
            $deliveryNoteId = $this->repo->saveDeliveryNote($deliveryNote, true);

            $deliveryNoteProducts = $this->factory->createDeliveryNoteProducts(
                $deliveryNoteId,
                $data->deliveryNoteProducts,
            );
            $this->repo->saveDeliveryNoteProducts($deliveryNoteProducts, true);

            return new Success("DeliveryNoteCreated", ['id' => $deliveryNoteId]);
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

        if ($data->customerId) {
            $deliveryNote->customer = $this->customerService->findById($data->customerId);
        }

        if ($data->deliveryDate) {
            $deliveryNote->deliveryDate = new \DateTimeImmutable($data->deliveryDate);
        }

        if ($data->delivery !== null) {
            $deliveryNote->delivery = $data->delivery;
        }

        if ($data->status !== null) {
            $deliveryNote->status = $data->status;
        }

        $deliveryNote->shortDescription = $data->shortDescription;
        $deliveryNote->assignment = $data->assignment;

        if ($data->deliveryNoteProducts) {
            // $deliveryNote->deliveryNoteProducts = $data->deliveryNoteProducts;
            $deliveryNote->deliveryNoteProducts = $this->factory->createDeliveryNoteProducts(
                $id,
                $data->deliveryNoteProducts,
            );
            // $this->repo->saveDeliveryNoteProducts($deliveryNoteProducts, true);
        }

        if ($data->removedProductIds) {
                $this->repo->removeDeliveryNoteProducts($data->removedProductIds);
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

    public function createReturnNote(CreateReturnNoteRequestDto $data): Error|Success
    {
        $deliveryNote = $this->repo->findById($data->deliveryNoteId);

        if (!$deliveryNote) {
            return new Error("DeliveryNote not found", 404);
        }

        $entries = $this->factory->createReturnNoteEntries($data->returnNoteEntries);

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
}
