<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssuanceType extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'description',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(Issuance::class);
    }
}