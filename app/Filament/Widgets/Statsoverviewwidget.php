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

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalItems        = Item::count();
        $totalCategories   = Category::count();
        $totalSites        = Site::count();
        $totalVariants     = ItemVariant::count();
        $totalStock        = ItemVariant::sum('quantity');
        $outOfStockCount   = ItemVariant::where('quantity', 0)->count();

        // moq is a computed PHP attribute — must filter in PHP, not SQL
        $lowStockIds = cache()->remember('low_stock_ids', now()->addMinutes(5), fn () =>
            ItemVariant::all()
                ->filter(fn ($v) => $v->quantity <= $v->moq)
                ->pluck('id')
        );

        $lowStockCount     = $lowStockIds->count();

        $pendingIssuances  = Issuance::where('status', 'pending')->count();
        $releasedIssuances = Issuance::where('status', 'released')->count();
        $issuedCount       = Issuance::where('status', 'issued')->count();

        $pendingRestocks   = Restock::where('status', 'pending')->count();
        $partialRestocks   = Restock::where('status', 'partial')->count();

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