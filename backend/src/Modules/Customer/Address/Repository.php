<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use Doctrine\ORM\EntityManagerInterface;
use App\Modules\Customer\Address\CustomerAddress;

class Repository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(CustomerAddress $address, bool $flush = false): void
    {
        $this->em->persist($address);
        if ($flush) {
            $this->em->flush();
        }
    }

    // Add list/find helpers as needed
    public function findAll(): array
    {
        $customerAddresses = $this->em->getRepository(CustomerAddress::class)->findAll();
        return $customerAddresses;
    }

    public function find(string $id): ?CustomerAddress
    {
        return $this->em->getRepository(CustomerAddress::class)->find($id);
    }
}
