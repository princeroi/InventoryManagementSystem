<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformIssuanceReturnItem extends Model
{
    protected $fillable = [
        'uniform_issuance_recipient_id',
        'item_id',
        'size',
        'quantity',
        'returned_by',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(UniformIssuanceRecipient::class, 'uniform_issuance_recipient_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}