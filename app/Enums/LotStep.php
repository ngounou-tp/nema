<?php

namespace App\Enums;

/**
 * Traceability steps of a lot (`lots.steps[].label`). Stored as English keys, labelled per locale.
 */
enum LotStep: string
{
    case Harvest = 'harvest';
    case Fermentation = 'fermentation';
    case Drying = 'drying';
    case Pulping = 'pulping';
    case Hulling = 'hulling';
    case Shelling = 'shelling';
    case Sorting = 'sorting';
    case Bagging = 'bagging';
    case Packaging = 'packaging';
    case QualityCheck = 'quality_check';
    case Storage = 'storage';
    case Transport = 'transport';
    case Shipping = 'shipping';
    case Delivery = 'delivery';

    /**
     * Translated label, in the current locale (lang/{fr,en}/enums.php).
     */
    public function label(): string
    {
        return __('enums.lot_step.'.$this->value);
    }
}
