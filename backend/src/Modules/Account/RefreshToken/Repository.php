<?php

declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Modules\Account\Account;
use App\Shared\Doctrine\UuidStringType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findByHash(string $hash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    public function add(RefreshToken $token): void
    {
        $this->getEntityManager()->persist($token);
    }

    public function revokeFamily(string $familyId, \DateTimeImmutable $now): int
    {
        return (int) $this->createQueryBuilder('t')
            ->update()
            ->set('t.revokedAt', ':now')
            ->where('t.familyId = :family')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('now', $now, 'datetime_immutable')
            ->setParameter('family', $familyId, UuidStringType::NAME)
            ->getQuery()
            ->execute();
    }

    public function revokeAllForAccount(Account $account, \DateTimeImmutable $now, ?string $exceptFamilyId = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->update()
            ->set('t.revokedAt', ':now')
            ->where('t.account = :account')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('now', $now, 'datetime_immutable')
            ->setParameter('account', $account->id, UuidStringType::NAME);

        if ($exceptFamilyId !== null) {
            $qb->andWhere('t.familyId <> :family')->setParameter('family', $exceptFamilyId, UuidStringType::NAME);
        }

        return (int) $qb->getQuery()->execute();
    }

    public function deleteExpired(\DateTimeImmutable $before): int
    {
        return (int) $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :before OR t.sessionExpiresAt < :before OR t.revokedAt < :before')
            ->setParameter('before', $before, 'datetime_immutable')
            ->getQuery()
            ->execute();
    }
}
