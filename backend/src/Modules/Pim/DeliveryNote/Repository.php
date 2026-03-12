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
        return $this->findBy(['status' => $status]);
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

    public function remove(DeliveryNote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeDeliveryNoteProducts(array $deliveryNoteProductIds): void
    {
        foreach ($deliveryNoteProductIds as $id) {
            $entity = $this->getEntityManager()->getRepository(DeliveryNoteProduct::class)->find($id);

            if ($entity) {
                $this->getEntityManager()->remove($entity);
            }
        }

        $this->getEntityManager()->flush();
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
