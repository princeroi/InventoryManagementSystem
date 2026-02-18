<?php

namespace App\Filament\Widgets;

use App\Models\ItemVariant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Low & Out of Stock Items';

    public function table(Table $table): Table
    {
        $lowStockIds = cache()->remember('low_stock_ids', now()->addMinutes(5), fn () =>
            ItemVariant::with('item:id,name')
                ->get()
                ->filter(fn ($variant) => $variant->quantity <= $variant->moq)
                ->pluck('id')
        );

        return $table
            ->query(
                ItemVariant::query()
                    ->with('item:id,name')
                    ->whereIn('id', $lowStockIds)
                    ->orderBy('quantity')
            )
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable(),

                TextColumn::make('size_label')
                    ->label('Size'),

                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('moq')
                    ->label('MOQ')
                    ->getStateUsing(fn ($record) => $record->moq),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->stock_status)
                    ->colors([
                        'danger'  => 'Out of Stock',
                        'warning' => 'Low Stock',
                        'success' => 'Enough Stock',
                    ]),
            ])
            ->paginated([5, 10, 25]);
    }
}