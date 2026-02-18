<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuanceLog extends Model
{
    protected $fillable = [
        'issuance_id',
        'action',
        'performed_by',
        'note',
    ];

    public function issuance(): BelongsTo
    {
        return $this->belongsTo(Issuance::class);
    }
}
