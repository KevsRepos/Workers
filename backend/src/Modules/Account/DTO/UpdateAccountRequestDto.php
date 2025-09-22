<?php declare(strict_types=1);

namespace App\Modules\Account\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateAccountRequestDto {
    #[Assert\Email(message: "EmailAddressNotValid")]
    public ?string $emailAddress = null;

    #[Assert\Length(min: 2, max: 50, minMessage: "FirstNameTooShort", maxMessage: "FirstNameTooLong")]
    public ?string $firstName = null;

    #[Assert\Length(min: 2, max: 50, minMessage: "SurnameTooShort", maxMessage: "SurnameTooLong")]
    public ?string $surname = null;

    #[Assert\Length(min: 8, max: 50, minMessage: "PasswordTooShort", maxMessage: "PasswordTooLong")]
    public ?string $password = null;
}
