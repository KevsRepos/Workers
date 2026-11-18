<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

use App\Shared\Validation\StrongPassword;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordRequest
{
    #[Assert\NotBlank]
    public string $currentPassword = '';

    #[StrongPassword]
    public string $newPassword = '';

    #[Assert\NotBlank]
    #[Assert\EqualTo(propertyPath: 'newPassword', message: 'Passwords do not match.')]
    public string $confirmNewPassword = '';

    /** Refresh token of the current session; kept alive while all other sessions are revoked. */
    public ?string $refreshToken = null;
}
