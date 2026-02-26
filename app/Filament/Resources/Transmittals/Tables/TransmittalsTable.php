<?php

namespace App\Filament\Resources\Transmittals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransmittalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transmittal_number')
                    ->label('Transmittal #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('transmitted_by')
                    ->label('Transmitted By (Manual)')
                    ->searchable(),

                TextColumn::make('transmittedByUser.name')
                    ->label('Transmitted By (User)')
                    ->searchable(),

                TextColumn::make('transmitted_to')
                    ->label('Transmitted To')
                    ->searchable(),

                TextColumn::make('items_summary')
                    ->label('Items Summary')
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('update transmittal')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('delete-any transmittal')),
                ]),
            ]);
    }
}