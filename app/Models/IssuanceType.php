<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuanceType extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'description',
        'is_salary_deduct',
    ];

    protected $casts = [
        'is_salary_deduct' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}