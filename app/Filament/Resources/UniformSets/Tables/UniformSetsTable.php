<?php

namespace App\Filament\Resources\UniformSets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;

class UniformSetsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position.name')
                    ->label('Position')
                    ->sortable(),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                // ── NEW: employee status badge ──────────────────────────────
                TextColumn::make('employee_status')
                    ->label('For Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'posted'   => 'Posted',
                        'reliever' => 'Reliever',
                        default    => 'All',
                    })
                    ->color(fn ($state) => match ($state) {
                        'posted'   => 'success',
                        'reliever' => 'warning',
                        default    => 'gray',
                    }),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // ── NEW: filter by employee status ──────────────────────────
                SelectFilter::make('employee_status')
                    ->label('Employee Status')
                    ->options([
                        'posted'   => 'Posted',
                        'reliever' => 'Reliever',
                    ])
                    ->placeholder('All'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}