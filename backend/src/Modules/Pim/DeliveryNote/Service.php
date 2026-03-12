<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use Error;
use Exception;
use App\Modules\Pim\DeliveryNote\Dto\CreateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Dto\UpdateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Repository;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Modules\Customer\Service as CustomerService;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory,
        private CustomerService $customerService,
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
}
