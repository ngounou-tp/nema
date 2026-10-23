<?php

namespace App\Models;

use App\Enums\Region;
use Database\Factories\CooperativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Region $region
 * @property list<string> $filieres
 * @property int|null $manager_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'region', 'filieres', 'manager_id'])]
class Cooperative extends Model
{
    /** @use HasFactory<CooperativeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'region' => Region::class,
            'filieres' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * @return HasMany<Producer, $this>
     */
    public function producers(): HasMany
    {
        return $this->hasMany(Producer::class);
    }
}
