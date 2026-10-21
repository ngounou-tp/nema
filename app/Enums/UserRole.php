<?php

namespace App\Enums;

enum UserRole: string
{
    case Producer = 'producer';
    case Cooperative = 'cooperative';
    case Buyer = 'buyer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Producer => 'Producteur',
            self::Cooperative => 'Coopérative / GIC',
            self::Buyer => 'Acheteur',
            self::Admin => 'Administrateur',
        };
    }
}
