<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeSupplyRequest extends Model
{
    protected $fillable = [
        'department_id',
        'employee_id',
        'requested_by',
        'request_date',
        'note',
        'status',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    /** The user this request is FOR */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /** The user who submitted the request */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfficeSupplyRequestItem::class);
    }
}