<?php

declare(strict_types=1);

namespace App\Modules\Account;

use App\Audit\Auditable;
use App\Shared\Domain\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Auditable(ignore: ['lastLoginAt'], redact: ['password'])]
#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'account')]
class Account extends Entity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180, unique: true)]
    public string $emailAddress;

    #[ORM\Column(length: 100)]
    public string $firstName;

    #[ORM\Column(length: 100)]
    public string $surname;

    #[ORM\Column(length: 255)]
    public string $password;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    public array $roles = [];

    #[ORM\Column(options: ['default' => true])]
    public bool $active = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $passwordChangedAt = null;

    public string $displayName {
        get => trim($this->firstName . ' ' . $this->surname);
    }

    public function __construct(string $emailAddress, string $firstName, string $surname)
    {
        $this->emailAddress = self::normalizeEmail($emailAddress);
        $this->firstName = trim($firstName);
        $this->surname = trim($surname);
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_values(array_unique([...$this->roles, Role::USER]));
    }

    public function isAdmin(): bool
    {
        return \in_array(Role::ADMIN, $this->roles, true);
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        $this->assignIdentity();

        return (string) $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
