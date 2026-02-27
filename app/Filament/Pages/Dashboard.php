<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        // employees with this role CANNOT access
        if ($user->hasRole('employee')) {
            return false;
        }

        return true;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\LowStockWidget::class,
            \App\Filament\Widgets\RecentIssuancesWidget::class,
            \App\Filament\Widgets\RecentRestocksWidget::class,
        ];
    }
}