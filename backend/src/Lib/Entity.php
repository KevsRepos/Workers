<?php declare(strict_types=1);

namespace App\Lib;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

abstract class Entity
{
    #[ORM\Id()]
    #[ORM\Column(type: 'uuid', unique: true)]
    public ?string $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->id) {
            $this->id = Uuid::v7()->toBinary();
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}