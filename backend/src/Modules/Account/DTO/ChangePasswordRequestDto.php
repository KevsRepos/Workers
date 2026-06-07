<?php declare(strict_types=1);

namespace App\Modules\Account\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordRequestDto {
    #[Assert\NotBlank]
    public string $oldPassword;

    #[Assert\Length(min: 8, max: 50, minMessage: "PasswordTooShort", maxMessage: "PasswordTooLong")]
    #[Assert\NotBlank]
    public string $newPassword;

    #[Assert\NotBlank]
    public string $confirmNewPassword;
}
