<?php

declare(strict_types=1);

namespace App\Shared\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

/**
 * Password policy aligned with NIST SP 800-63B / BSI recommendations:
 * minimum length 12, long passphrases allowed, entropy-based strength check, no composition rules.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class StrongPassword extends Compound
{
    public const int MIN_LENGTH = 12;
    public const int MAX_LENGTH = 4096;

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(message: 'Password must not be empty.'),
            new Assert\Length(
                min: self::MIN_LENGTH,
                max: self::MAX_LENGTH,
                minMessage: 'Password must be at least {{ limit }} characters long.',
                maxMessage: 'Password must not exceed {{ limit }} characters.',
            ),
            new Assert\PasswordStrength(
                minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                message: 'Password is too weak. Use a longer passphrase or more varied characters.',
            ),
        ];
    }
}
