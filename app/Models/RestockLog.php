<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockLog extends Model
{
     protected $fillable = [
        'restock_id',
        'action',
        'performed_by',
        'note',
    ];

    public function restock(): BelongsTo
    {
        return $this->belongsTo(Restock::class);
    }
}
