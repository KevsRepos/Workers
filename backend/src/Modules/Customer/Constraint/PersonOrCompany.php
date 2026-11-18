<?php

declare(strict_types=1);

namespace App\Modules\Customer\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * A customer is either a person (first name + surname) or a company (company name).
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PersonOrCompany extends Constraint
{
    public string $message = 'Provide either first name and surname or a company name.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
