<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['name', 'description', 'department_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }


    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
