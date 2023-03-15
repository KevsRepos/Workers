<?php declare(strict_types=1);

namespace App\Service;

use App\Service\StringNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccountAsserter {
    #[Assert\Email]
    private string $emailAddress;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $surname;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $rawPassword;
    
    private string $hashedPassword;

    public function __construct(private StringNormalizer $normalize, private ValidatorInterface $validator)
    {   
    }

    public function validate(): ConstraintViolationListInterface
    {
        return $this->validator->validate($this);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress = ""): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName = ""): self
    {
        $this->firstName = $this->normalize->asNoun($firstName);

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname = ""): self
    {
        $this->surname = $this->normalize->asNoun($surname);

        return $this;
    }

    public function setRawPassword(string $rawPassword): self
    {
        $this->rawPassword = $rawPassword;

        return $this;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword(): self
    {
        $this->hashedPassword = password_hash($this->rawPassword, PASSWORD_DEFAULT);

        return $this;
    }
}