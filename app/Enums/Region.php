<?php

namespace App\Enums;

/**
 * The ten regions of Cameroon. Stored as English keys, labelled per locale.
 */
enum Region: string
{
    case Adamawa = 'adamawa';
    case Centre = 'centre';
    case East = 'east';
    case FarNorth = 'far_north';
    case Littoral = 'littoral';
    case North = 'north';
    case NorthWest = 'north_west';
    case West = 'west';
    case South = 'south';
    case SouthWest = 'south_west';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.region.'.$this->value);
    }
}
