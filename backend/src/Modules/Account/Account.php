<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\Repository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: Repository::class)]
class Account implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $accountId = null;

    #[ORM\Column(length: 255, unique: true)]
    public ?string $emailAddress = null;

    #[ORM\Column(length: 100)]
    public ?string $firstName = null;

    #[ORM\Column(length: 100)]
    public ?string $surname = null;

    #[ORM\Column(length: 255)]
    public ?string $password = null;

    #[ORM\Column]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public function getRoles(): array
    {
        return [];        
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->emailAddress;
    }

    /**lexik jwt needs this to get the id */
    public function getId(): ?int
    {
        return $this->accountId;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

	public function getEmailAddress(): string
	{
		return $this->emailAddress;
	}
}
