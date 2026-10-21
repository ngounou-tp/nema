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

    public function label(): string
    {
        return match ($this) {
            self::Cacao => 'Cacao',
            self::CafeArabica => 'Café arabica',
            self::CafeRobusta => 'Café robusta',
            self::Mais => 'Maïs',
            self::Manioc => 'Manioc',
            self::Plantain => 'Plantain',
            self::Banane => 'Banane',
            self::Tomate => 'Tomate',
            self::Arachide => 'Arachide',
            self::Igname => 'Igname',
            self::HuileDePalme => 'Huile de palme',
        };
    }
}
