<?php

namespace App\Filament\Widgets;

use App\Models\Restock;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class RecentRestocksWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent Restocks';

    // ── Spatie permission helper ──────────────────────────────────────────

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Visibility ────────────────────────────────────────────────────────

    public static function canView(): bool
    {
        return static::userCan('view recent-restocks-widget');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Restock::query()
                    ->when(Filament::getTenant(), fn ($q, $tenant) =>
                        $q->where('department_id', $tenant->id)
                    )
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('ordered_by')
                    ->label('Ordered By'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'partial',
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                        'info'    => 'returned',
                    ]),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since(),
            ])
            ->paginated(false);
    }
}