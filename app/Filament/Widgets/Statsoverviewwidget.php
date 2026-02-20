<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Issuance;
use App\Models\Restock;
use App\Models\Site;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        // Get item IDs for this tenant
        $itemIds = $tenant ? $tenant->items()->pluck('items.id') : null;
        $hasItems = $itemIds && $itemIds->isNotEmpty();

        // If tenant exists but has no items, show zeros
        $variantQuery = ItemVariant::query();
        if ($tenant) {
            if ($hasItems) {
                $variantQuery->whereIn('item_id', $itemIds);
            } else {
                $variantQuery->whereRaw('0 = 1'); // explicit empty — no variants
            }
        }

        $totalItems      = $hasItems ? $itemIds->count() : ($tenant ? 0 : Item::count());
        $totalCategories = $tenant ? Category::where('department_id', $tenant->id)->count() : Category::count();
        $totalSites      = Site::count();
        $totalVariants   = (clone $variantQuery)->count();
        $totalStock      = (clone $variantQuery)->sum('quantity');
        $outOfStockCount = (clone $variantQuery)->where('quantity', 0)->count();

        $lowStockIds = cache()->remember("low_stock_ids_{$tenant?->id}", now()->addMinutes(5), fn () =>
            $hasItems
                ? (clone $variantQuery)->get()
                    ->filter(fn ($v) => $v->quantity <= $v->moq)
                    ->pluck('id')
                : collect()
        );

        $lowStockCount = $lowStockIds->count(); // ← now always defined

        $issuanceQuery = Issuance::query()
            ->when($tenant, fn ($q) => $q->where('department_id', $tenant->id));

        $restockQuery = Restock::query()
            ->when($tenant, fn ($q) => $q->where('department_id', $tenant->id));

        $pendingIssuances  = (clone $issuanceQuery)->where('status', 'pending')->count();
        $releasedIssuances = (clone $issuanceQuery)->where('status', 'released')->count();
        $issuedCount       = (clone $issuanceQuery)->where('status', 'issued')->count();
        $pendingRestocks   = (clone $restockQuery)->where('status', 'pending')->count();
        $partialRestocks   = (clone $restockQuery)->where('status', 'partial')->count();


        return [
            Stat::make('Total Items', $totalItems)
                ->description("{$totalCategories} categories · {$totalVariants} variants")
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Total Stock', number_format($totalStock))
                ->description("{$lowStockCount} low · {$outOfStockCount} out of stock")
                ->descriptionIcon('heroicon-m-archive-box')
                ->color($outOfStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success')),

            Stat::make('Pending Issuances', $pendingIssuances)
                ->description("{$releasedIssuances} released · {$issuedCount} issued")
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color($pendingIssuances > 0 ? 'warning' : 'success'),

            Stat::make('Pending Restocks', $pendingRestocks)
                ->description("{$partialRestocks} partially delivered")
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingRestocks > 0 ? 'warning' : 'success'),
        ];
    }
}