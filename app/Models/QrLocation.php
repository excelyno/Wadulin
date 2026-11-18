<?php

namespace App\Models;

use Database\Factories\QrLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['district_id', 'code', 'name', 'address', 'latitude', 'longitude', 'is_active', 'created_by'])]
class QrLocation extends Model
{
    /** @use HasFactory<QrLocationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Complaint, $this>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Limit the query to QR points the given officer may manage.
     *
     * @param  Builder<self>  $query
     */
    public function scopeManageableBy(Builder $query, User $user): void
    {
        if (! $user->isAdminKabupaten()) {
            $query->where('district_id', $user->district_id);
        }
    }

    /**
     * Generate a unique, hard-to-guess code printed on the QR poster.
     */
    public static function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
