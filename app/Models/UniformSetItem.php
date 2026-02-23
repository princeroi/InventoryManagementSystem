<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformSetItem extends Model
{
    protected $fillable = [
        'uniform_set_id',
        'item_id',
        'size',
        'quantity',
    ];

    public function uniformSet(): BelongsTo
    {
        return $this->belongsTo(UniformSet::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
