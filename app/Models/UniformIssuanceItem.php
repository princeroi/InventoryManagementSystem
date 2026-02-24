<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformIssuanceItem extends Model
{
    // ── Static flag to skip stock events during bulk create flow ─────────────
    public static bool $skipStockEvents = false;

    protected $fillable = [
        'uniform_issuance_recipient_id',
        'item_id',
        'size',
        'quantity',
        'released_quantity',
        'remaining_quantity',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(UniformIssuanceRecipient::class, 'uniform_issuance_recipient_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected static function booted(): void
    {
        // ── created ──────────────────────────────────────────────────────────
        // Only fires for EDIT flow (adding a new item row to an existing issuance).
        // CREATE flow sets skipStockEvents = true before creating items,
        // then handles deduction manually after all items are saved.
        static::created(function (self $item) {
            if (self::$skipStockEvents) return;

            $issuance = $item->recipient?->uniformIssuance;
            if (! $issuance) return;

            if (in_array($issuance->status, ['issued', 'released'])) {
                self::adjustStock($item, 'decrement');
            }
        });

        // ── deleted ──────────────────────────────────────────────────────────
        static::deleted(function (self $item) {
            if (self::$skipStockEvents) return;

            $issuance = $item->recipient?->uniformIssuance;
            if (! $issuance) return;

            if (in_array($issuance->status, ['issued', 'released'])) {
                self::adjustStock($item, 'increment');
            }
        });

        // ── updated ──────────────────────────────────────────────────────────
        static::updated(function (self $item) {
            if (self::$skipStockEvents) return;
            if (! $item->isDirty('quantity')) return;

            $issuance = $item->recipient?->uniformIssuance;
            if (! $issuance) return;

            if (! in_array($issuance->status, ['issued', 'released'])) return;

            $oldQty = $item->getOriginal('quantity');
            $newQty = $item->quantity;
            $diff   = $newQty - $oldQty;

            if ($diff === 0) return;

            $variant = ItemVariant::where('item_id', $item->item_id)
                ->where('size_label', $item->size)
                ->first();

            if (! $variant) return;

            $diff > 0
                ? $variant->decrement('quantity', $diff)
                : $variant->increment('quantity', abs($diff));
        });
    }

    private static function adjustStock(self $item, string $direction): void
    {
        $variant = ItemVariant::where('item_id', $item->item_id)
            ->where('size_label', $item->size)
            ->first();

        if (! $variant) return;

        $direction === 'decrement'
            ? $variant->decrement('quantity', $item->quantity)
            : $variant->increment('quantity', $item->quantity);
    }
}