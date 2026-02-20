<?php

namespace App\Filament\Widgets;

use App\Models\Issuance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Facades\Filament;

class RecentIssuancesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent Issuances';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Issuance::query()
                    ->with('site:id,name')
                    ->when(Filament::getTenant(), fn ($q, $tenant) =>
                        $q->where('department_id', $tenant->id)
                    )
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('issued_to')
                    ->label('Issued To')
                    ->searchable(),

                TextColumn::make('site.name')
                    ->label('Site'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'released',
                        'success' => 'issued',
                        'danger'  => 'cancelled',
                        'gray'    => 'returned',
                    ]),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since(),
            ])
            ->paginated(false);
    }
}