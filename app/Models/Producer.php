<?php

namespace App\Models;

use Database\Factories\ProducerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $cooperative_id
 * @property string $village
 * @property string $surface_ha
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'cooperative_id', 'village', 'surface_ha'])]
class Producer extends Model
{
    /** @use HasFactory<ProducerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'surface_ha' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Cooperative, $this>
     */
    public function cooperative(): BelongsTo
    {
        return $this->belongsTo(Cooperative::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasManyThrough<Order, Product, $this>
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    /**
     * @return HasManyThrough<Lot, Product, $this>
     */
    public function lots(): HasManyThrough
    {
        return $this->hasManyThrough(Lot::class, Product::class);
    }

    /**
     * @return HasOne<SupplierScore, $this>
     */
    public function supplierScore(): HasOne
    {
        return $this->hasOne(SupplierScore::class);
    }
}
