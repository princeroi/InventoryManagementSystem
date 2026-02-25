<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniformIssuanceRecipient extends Model
{
    protected $fillable = [
        'uniform_issuance_id',
        'transaction_id',
        'employee_name',
        'position_id',
        'uniform_set_id',
        'mode',
        'employee_status',
    ];

    // ── Auto-generate transaction_id on create ────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (self $recipient) {
            if (empty($recipient->transaction_id)) {
                $recipient->transaction_id = self::generateTransactionId();
            }
        });
    }

    public static function generateTransactionId(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "TXN-{$date}-";

        $last = static::where('transaction_id', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(transaction_id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
            ->value('transaction_id');

        $next = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function uniformIssuance(): BelongsTo
    {
        return $this->belongsTo(UniformIssuance::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function uniformSet(): BelongsTo
    {
        return $this->belongsTo(UniformSet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(UniformIssuanceItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(UniformIssuanceReturnItem::class);
    }
}