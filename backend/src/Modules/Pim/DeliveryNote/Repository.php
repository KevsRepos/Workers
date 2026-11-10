<?php

namespace App\Modules\Pim\DeliveryNote;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<DeliveryNote>
 *
 * @method DeliveryNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryNote[]    findAll()
 * @method DeliveryNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryNote::class);
    }

    public function findById(string $id): ?DeliveryNote
    {
        return $this->find($id);
    }

    /**
     * @return DeliveryNote[]
     */
    public function findByStatus(DeliveryNoteStatus $status): array
    {
        return $this->findBy(['status' => $status], ['deliveryDate' => 'DESC']);
    }

    public function saveDeliveryNote(DeliveryNote $entity, bool $flush = false): string
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $entity->id;
    }

    public function saveDeliveryNoteProducts(ArrayCollection $entities, bool $flush = false): void
    {
        foreach ($entities as $entity) {
            $this->getEntityManager()->persist($entity);
        }
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function saveReturnNoteEntries(ArrayCollection $entries, bool $flush = false): void
    {
        foreach ($entries as $entry) {
            $this->getEntityManager()->persist($entry);
        }
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DeliveryNote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string[] $deliveryNoteProductIds
     * @return DeliveryNoteProduct[]
     */
    public function findDeliveryNoteProducts(DeliveryNote $deliveryNote, array $deliveryNoteProductIds): array
    {
        if ($deliveryNoteProductIds === []) {
            return [];
        }

        return $this->getEntityManager()->getRepository(DeliveryNoteProduct::class)->findBy([
            'id' => array_values(array_unique($deliveryNoteProductIds)),
            'deliveryNote' => $deliveryNote,
        ]);
    }

    /**
     * @param string[] $deliveryNoteProductIds
     */
    public function removeDeliveryNoteProducts(DeliveryNote $deliveryNote, array $deliveryNoteProductIds): bool
    {
        $entities = $this->findDeliveryNoteProducts($deliveryNote, $deliveryNoteProductIds);
        if (count($entities) !== count($deliveryNoteProductIds)) {
            return false;
        }

        foreach ($entities as $entity) {
            $this->getEntityManager()->remove($entity);
        }

        return true;
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
