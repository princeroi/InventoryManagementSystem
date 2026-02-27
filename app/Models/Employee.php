<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'employee_id',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function officeSupplyRequests(): HasMany
    {
        return $this->hasMany(OfficeSupplyRequest::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Computed label for dropdowns ──────────────────────────────────────────
    public function getDisplayLabelAttribute(): string
    {
        $parts = [$this->name];
        if ($this->position) {
            $parts[] = "— {$this->position}";
        }
        if ($this->employee_id) {
            $parts[] = "[{$this->employee_id}]";
        }
        return implode(' ', $parts);
    }
}