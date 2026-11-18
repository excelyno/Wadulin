<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

#[Fillable([
    'ticket_code',
    'user_id',
    'category_id',
    'district_id',
    'qr_location_id',
    'title',
    'description',
    'address',
    'latitude',
    'longitude',
    'status',
    'is_anonymous',
    'due_at',
    'escalated_at',
    'resolved_at',
    'rating',
    'feedback',
])]
class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ComplaintStatus::class,
            'is_anonymous' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'due_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_code';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return BelongsTo<QrLocation, $this>
     */
    public function qrLocation(): BelongsTo
    {
        return $this->belongsTo(QrLocation::class);
    }

    /**
     * @return HasMany<ComplaintAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    /**
     * @return HasMany<ComplaintStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ComplaintStatusHistory::class);
    }

    /**
     * @return HasMany<ComplaintDisposition, $this>
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(ComplaintDisposition::class);
    }

    /**
     * Limit the query to complaints the given user is allowed to see.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        match ($user->role) {
            UserRole::AdminKabupaten => null,
            UserRole::AdminKecamatan => $query->where('district_id', $user->district_id),
            UserRole::Masyarakat => $query->where('user_id', $user->id),
        };
    }

    /**
     * Open complaints whose SLA deadline has passed.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereIn('status', ComplaintStatus::open())->where('due_at', '<', now());
    }

    public function isEscalated(): bool
    {
        return $this->escalated_at !== null && ! $this->status->isFinal();
    }

    /**
     * Build the next ticket code for the current year, e.g. WDL-2026-000123.
     */
    public static function nextTicketCode(): string
    {
        $prefix = 'WDL-'.now()->year.'-';

        $lastCode = static::withTrashed()
            ->where('ticket_code', 'like', $prefix.'%')
            ->orderByDesc('ticket_code')
            ->value('ticket_code');

        $nextNumber = $lastCode === null ? 1 : ((int) substr($lastCode, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Add the configured number of working days (Mon-Fri) to a moment.
     */
    public static function calculateDueAt(Carbon $from): Carbon
    {
        return $from->copy()->addWeekdays(config('wadulin.sla_working_days'));
    }

    /**
     * Move the complaint to a new status and record it in the audit trail.
     */
    public function transitionTo(ComplaintStatus $target, User $actor, ?string $note = null, bool $isPublicNote = true): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new InvalidArgumentException("Transisi status {$this->status->value} ke {$target->value} tidak diizinkan.");
        }

        $this->recordStatusChange($target, $actor, $note, $isPublicNote);
    }

    /**
     * Let the reporter withdraw a complaint that no officer has picked up yet.
     */
    public function cancelBy(User $reporter, ?string $reason = null): void
    {
        if ($this->status !== ComplaintStatus::Pending) {
            throw new InvalidArgumentException('Hanya pengaduan berstatus Pending yang dapat dibatalkan.');
        }

        $this->recordStatusChange(ComplaintStatus::Dibatalkan, $reporter, $reason, true);
    }

    /**
     * Persist a status change together with its audit trail entry.
     */
    private function recordStatusChange(ComplaintStatus $target, User $actor, ?string $note, bool $isPublicNote): void
    {
        DB::transaction(function () use ($target, $actor, $note, $isPublicNote): void {
            $fromStatus = $this->status;

            $this->status = $target;

            if ($target->isFinal()) {
                $this->resolved_at = now();
                $this->escalated_at = null;
            }

            $this->save();

            $this->statusHistories()->create([
                'changed_by' => $actor->id,
                'from_status' => $fromStatus,
                'to_status' => $target,
                'note' => $note,
                'is_public' => $isPublicNote,
            ]);
        });
    }

    /**
     * Hand the complaint over to another district and restart its SLA.
     */
    public function disposeTo(District $targetDistrict, User $actor, string $reason): void
    {
        if ($targetDistrict->is($this->district)) {
            throw new InvalidArgumentException('Kecamatan tujuan sama dengan kecamatan saat ini.');
        }

        DB::transaction(function () use ($targetDistrict, $actor, $reason): void {
            $this->dispositions()->create([
                'from_district_id' => $this->district_id,
                'to_district_id' => $targetDistrict->id,
                'moved_by' => $actor->id,
                'reason' => $reason,
            ]);

            $this->district()->associate($targetDistrict);
            $this->due_at = static::calculateDueAt(now());
            $this->escalated_at = null;
            $this->save();
        });
    }
}
