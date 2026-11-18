<?php

declare(strict_types=1);

namespace App\Modules\Account;

/**
 * Application roles. See docs/compliance/berechtigungskonzept.md.
 *
 * ROLE_USER  – operational work: customers, delivery notes, own time sheets, read master data.
 * ROLE_ADMIN – additionally: master data (products, prices, deposits, unions, presets),
 *              account administration, all time sheets, audit log.
 */
final class Role
{
    public const string USER = 'ROLE_USER';
    public const string ADMIN = 'ROLE_ADMIN';

    public const array ASSIGNABLE = [self::USER, self::ADMIN];
}
