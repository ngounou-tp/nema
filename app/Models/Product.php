<?php

namespace App\Models;

use App\Enums\Filiere;
use App\Enums\QualityGrade;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $producer_id
 * @property Filiere $filiere
 * @property string $name
 * @property string|null $description
 * @property string $quantity_kg
 * @property int $price_xaf
 * @property QualityGrade $quality_grade
 * @property bool $is_available
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['producer_id', 'filiere', 'name', 'description', 'quantity_kg', 'price_xaf', 'quality_grade', 'is_available'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
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
            'quantity_kg' => 'decimal:2',
            'price_xaf' => 'integer',
            'quality_grade' => QualityGrade::class,
            'is_available' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Producer, $this>
     */
    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    /**
     * @return HasMany<Lot, $this>
     */
    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
