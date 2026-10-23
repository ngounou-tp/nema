<?php

namespace App\Enums;

/**
 * Crop value chains. Stored as English keys, labelled per locale (lang/{fr,en}/enums.php).
 */
enum Filiere: string
{
    case Cacao = 'cocoa';
    case CafeArabica = 'arabica_coffee';
    case CafeRobusta = 'robusta_coffee';
    case Mais = 'maize';
    case Manioc = 'cassava';
    case Plantain = 'plantain';
    case Banane = 'banana';
    case Tomate = 'tomato';
    case Arachide = 'groundnut';
    case Igname = 'yam';
    case HuileDePalme = 'palm_oil';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.filiere.'.$this->value);
    }
}
