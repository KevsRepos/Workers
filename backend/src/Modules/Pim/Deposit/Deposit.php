<?php

declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Deposit (Pfand) amounts in euro cents.
 */
#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'deposit')]
class Deposit extends Entity
{
    #[ORM\Column(length: 100, nullable: true)]
    public ?string $label = null;

    /** Deposit per single unit (bottle) in cents. */
    #[ORM\Column]
    public int $singleAmount = 0;

    /** Deposit for the crate itself in cents. */
    #[ORM\Column(nullable: true)]
    public ?int $crateAmount = null;
}
