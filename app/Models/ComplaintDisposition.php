<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['complaint_id', 'from_district_id', 'to_district_id', 'moved_by', 'reason'])]
class ComplaintDisposition extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function fromDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'from_district_id');
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function toDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'to_district_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function mover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
