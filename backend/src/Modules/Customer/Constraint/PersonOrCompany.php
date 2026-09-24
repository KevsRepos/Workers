<?php declare(strict_types=1);

namespace App\Modules\Customer\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PersonOrCompany extends Constraint
{
    public string $message = 'Provide either firstName + surname or companyName, but not both.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
