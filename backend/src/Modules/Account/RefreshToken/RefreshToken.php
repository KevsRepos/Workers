<?php

declare(strict_types=1);

namespace App\Modules\Account\RefreshToken;

use App\Audit\Auditable;
use App\Modules\Account\Account;
use App\Shared\Doctrine\UuidStringType;
use App\Shared\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Opaque refresh token. Only a SHA-256 hash of the token is stored.
 *
 * Tokens of one login session share a family id. Every refresh rotates the token; presenting
 * an already rotated token is treated as theft and revokes the whole family.
 */
#[Auditable(enabled: false)]
#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\Index(name: 'idx_refresh_token_family', columns: ['family_id'])]
#[ORM\Index(name: 'idx_refresh_token_expires', columns: ['expires_at'])]
class RefreshToken extends Entity
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Account $account;

    #[ORM\Column(length: 64, unique: true, options: ['fixed' => true])]
    public string $tokenHash;

    #[ORM\Column(type: UuidStringType::NAME)]
    public string $familyId;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $expiresAt;

    /** Absolute end of the login session, independent of rotation. */
    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $sessionExpiresAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $rotatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $revokedAt = null;

    public function isUsable(\DateTimeImmutable $now): bool
    {
        return $this->rotatedAt === null
            && $this->revokedAt === null
            && $this->expiresAt > $now
            && $this->sessionExpiresAt > $now;
    }
}
