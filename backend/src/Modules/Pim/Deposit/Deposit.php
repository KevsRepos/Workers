<?php declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Deposit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    public ?string $depositId;

    #[ORM\Column]
    public int $singleAmount;

    #[ORM\Column]
    public ?int $crateAmount;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}