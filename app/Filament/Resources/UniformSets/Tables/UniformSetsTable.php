<?php

namespace App\Filament\Resources\UniformSets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
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
