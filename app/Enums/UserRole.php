<?php

namespace App\Enums;

enum UserRole: string
{
    case Producer = 'producer';
    case Cooperative = 'cooperative';
    case Buyer = 'buyer';
    case Admin = 'admin';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.user_role.'.$this->value);
    }
}
