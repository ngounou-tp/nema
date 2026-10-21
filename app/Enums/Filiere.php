<?php

namespace App\Enums;

enum Filiere: string
{
    case Cacao = 'cacao';
    case CafeArabica = 'cafe_arabica';
    case CafeRobusta = 'cafe_robusta';
    case Mais = 'mais';
    case Manioc = 'manioc';
    case Plantain = 'plantain';
    case Banane = 'banane';
    case Tomate = 'tomate';
    case Arachide = 'arachide';
    case Igname = 'igname';
    case HuileDePalme = 'huile_de_palme';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.filiere.'.$this->value);
    }
}
