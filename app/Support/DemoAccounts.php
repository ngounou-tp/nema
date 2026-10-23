<?php

namespace App\Support;

use App\Enums\UserRole;

/**
 * The four jury accounts ("Essayer en tant que…"), created by the DemoSeeder.
 */
final class DemoAccounts
{
    public const string PASSWORD = 'nema2026';

    /**
     * @var array<string, string> role value → email
     */
    public const array EMAILS = [
        'producer' => 'producteur@nema.demo',
        'cooperative' => 'coop@nema.demo',
        'buyer' => 'acheteur@nema.demo',
        'admin' => 'admin@nema.demo',
    ];

    public static function emailFor(UserRole $role): string
    {
        return self::EMAILS[$role->value];
    }
}
