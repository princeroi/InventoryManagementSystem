<?php

namespace App\Filament\Widgets;

use App\Models\Restock;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRestocksWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent Restocks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Restock::query()
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