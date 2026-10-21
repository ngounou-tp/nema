<?php

namespace App\Models;

use Database\Factories\LotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $product_id
 * @property string $code
 * @property Carbon $harvest_date
 * @property string $origin
 * @property list<array{label: string, date: string, location: string}> $steps
 * @property string|null $qr_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['product_id', 'code', 'harvest_date', 'origin', 'steps', 'qr_path'])]
class Lot extends Model
{
    /** @use HasFactory<LotFactory> */
    use HasFactory, HasUuids;

    /**
     * The public lot code is a UUID; the numeric id is never exposed in URLs.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['code'];
    }

    /**
     * Use a fully random UUID (v4) rather than a time-ordered one, as the code is public.
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'steps' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
