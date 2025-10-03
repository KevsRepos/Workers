<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Lib\Entity;
use App\Modules\Account\Repository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\HasLifecycleCallbacks]
class Account extends Entity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 255, unique: true)]
    public ?string $emailAddress = null;

    #[ORM\Column(length: 100)]
    public ?string $firstName = null;

    #[ORM\Column(length: 100)]
    public ?string $surname = null;

    #[ORM\Column(length: 255)]
    public ?string $password = null;

    public function getRoles(): array
    {
        return [];        
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->emailAddress;
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
