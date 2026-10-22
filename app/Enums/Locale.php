<?php

namespace App\Enums;

/**
 * Languages supported by NEMA: Cameroon's two official languages.
 */
enum Locale: string
{
    case French = 'fr';
    case English = 'en';

    public function label(): string
    {
        return __('enums.locale.'.$this->value);
    }
}
