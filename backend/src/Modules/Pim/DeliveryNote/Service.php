<?php declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use Error;
use Exception;
use App\Modules\Pim\DeliveryNote\Dto\CreateDeliveryNoteRequestDto;
use App\Modules\Pim\DeliveryNote\Repository;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service {
    public function __construct(
        private Repository $repo,
        private Factory $factory
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
        $deliveryNote = $this->factory->createDeliveryNote(
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
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("DeliveryNoteCreated");
    }
}
