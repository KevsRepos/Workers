<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Service\StringNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Asserter {
    #[Assert\Email(message: "EmailAddressNotValid")]
    private ?string $emailAddress = null;

    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "FirstNameTooShort",
        maxMessage: "FirstNameTooLong"
    )]
    private ?string $firstName = null;

    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "SurnameTooShort",
        maxMessage: "SurnameTooLong"
    )]
    private ?string $surname = null;

    #[Assert\Length(
        min: 12,
        max: 50,
        minMessage: "PasswordTooShort",
        maxMessage: "PasswordTooLong"
    )]
    private ?string $rawPassword = null;

    public function __construct(private StringNormalizer $normalize, private ValidatorInterface $validator)
    {   
    }

    public function validate(): array|false
    {
        $errors = $this->validator->validate($this);
        $arr = [];

        foreach ($errors as $value) {
            array_push($arr, $value->getMessage());
        }

        return count($arr) ? $arr : false;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress = ""): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName = ""): self
    {
        $this->firstName = $this->normalize->asNoun($firstName);

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname = ""): self
    {
        $this->surname = $this->normalize->asNoun($surname);

        return $this;
    }

    public function getRawPassword(): ?string
    {
        return $this->rawPassword;
    }

    public function setRawPassword(?string $rawPassword): self
    {
        $this->rawPassword = $rawPassword;

        return $this;
    }
}