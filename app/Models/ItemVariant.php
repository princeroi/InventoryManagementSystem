<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class ItemVariant extends Model
{
    protected $fillable =[
        'item_id',
        'size_label',
        'quantity',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getMoqAttribute(): int
    {
        $start = now()->startOfMonth()->subMonths(3);
        $end   = now()->endOfMonth();

        $totalDemand = \App\Models\IssuanceItem::where('item_id', $this->item_id)
            ->where('size', $this->size_label)
            ->whereHas('issuance', fn ($q) => $q
                ->whereIn('status', ['issued', 'released'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereDate('issued_at', '>=', $start)
                    ->whereDate('issued_at', '<=', $end)
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereDate('released_at', '>=', $start)
                            ->whereDate('released_at', '<=', $end);
                    })
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereDate('created_at', '>=', $start)
                            ->whereDate('created_at', '<=', $end);
                    });
                })
            )
            ->sum('quantity');

        // No history — use default minimum
        if ($totalDemand === 0) {
            return 20;
        }

        $leadTime       = 15;               // days to receive new stock
        $safeStock      = 30;               // fixed buffer stock
        $avgDailyDemand = $totalDemand / 90; // avg daily demand over 3 months (90 days)

        // MOQ = (Avg Daily Demand × Lead Time) + Safe Stock
        $moq = ($avgDailyDemand * $leadTime) + $safeStock;

        return max(20, (int) ceil($moq));
    }

    public function getStockStatusAttribute(): string
    {
        $moq = $this->moq;
        $qty = $this->quantity;

        if ($qty <= 0) return 'Out of Stock';
        if ($qty < $moq) return 'Low Stock';
        return 'Enough Stock';
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();
        $query = parent::getEloquentQuery()->with('item');

        if ($tenant) {
            $itemIds = $tenant->items()->pluck('items.id');
            $query->whereIn('item_id', $itemIds);
        }

        return $query;
    }

}
