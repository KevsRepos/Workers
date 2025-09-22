<?php declare(strict_types=1);

namespace App\Modules\Account\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAccountRequestDto {
    #[Assert\Email(message: "EmailAddressNotValid")]
    #[Assert\NotBlank]
    public string $emailAddress;

    #[Assert\Length(min: 2, max: 50, minMessage: "FirstNameTooShort", maxMessage: "FirstNameTooLong")]
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\Length(min: 2, max: 50, minMessage: "SurnameTooShort", maxMessage: "SurnameTooLong")]
    #[Assert\NotBlank]
    public string $surname;

    #[Assert\Length(min: 8, max: 50, minMessage: "PasswordTooShort", maxMessage: "PasswordTooLong")]
    #[Assert\NotBlank]
    public string $password;
}
