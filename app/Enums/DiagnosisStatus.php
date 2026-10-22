<?php

namespace App\Enums;

enum DiagnosisStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Uncertain = 'uncertain';
    case Failed = 'failed';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.diagnosis_status.'.$this->value);
    }
}
