<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use App\Shared\Doctrine\UuidStringType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Base class for all persisted entities: UUIDv7 identifier and timestamps.
 */
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class Entity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidStringType::NAME, unique: true)]
    public ?string $id = null;

    /** Nullable in PHP until assigned in PrePersist; never null in the database. */
    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->assignIdentity();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Assigns id and creation timestamp if not yet set. Safe to call multiple times.
     */
    public function assignIdentity(): void
    {
        $this->id ??= Uuid::v7()->toRfc4122();
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
