<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformIssuanceLog extends Model
{
    protected $fillable = [
        'uniform_issuance_id',
        'action',
        'performed_by',
        'note',
    ];

    public function uniformIssuance(): BelongsTo
    {
        return $this->belongsTo(UniformIssuance::class);
    }
}
