<?php

namespace App\Models;

use App\Enums\Filiere;
use Database\Factories\MarketPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Filiere $filiere
 * @property string $region
 * @property Carbon $date
 * @property int $price_xaf_kg
 * @property string $volume_t
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['filiere', 'region', 'date', 'price_xaf_kg', 'volume_t'])]
class MarketPrice extends Model
{
    /** @use HasFactory<MarketPriceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filiere' => Filiere::class,
            'date' => 'date',
            'price_xaf_kg' => 'integer',
            'volume_t' => 'decimal:2',
        ];
    }
}
