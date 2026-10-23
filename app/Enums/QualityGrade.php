<?php

namespace App\Enums;

enum QualityGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.quality_grade.'.$this->value);
    }
}
