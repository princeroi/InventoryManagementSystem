<?php

namespace App\Filament\Resources\ItemVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\IssuanceItem;
use Carbon\Carbon;

class ItemVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->searchable(),

                TextColumn::make('size_label')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('moq')
                    ->label('MOQ')
                    ->getStateUsing(fn ($record) => $record->moq),

                TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->stock_status)
                    ->colors([
                        'danger'  => 'Out of Stock',
                        'warning' => 'Low Stock',
                        'success' => 'Enough Stock',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([]);
    }
}