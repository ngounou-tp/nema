<?php

namespace App\Enums;

enum DiagnosisStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Uncertain = 'uncertain';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En cours',
            self::Completed => 'Terminé',
            self::Uncertain => 'Diagnostic incertain',
            self::Failed => 'Échec',
        };
    }
}
