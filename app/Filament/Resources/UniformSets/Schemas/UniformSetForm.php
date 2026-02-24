<?php

namespace App\Filament\Resources\UniformSets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Facades\Filament;

class UniformSetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),

                Select::make('position_id')
                    ->label('Position')
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Uniform Items')
                    ->relationship('items')
                    ->columnSpanFull()
                    ->columns(2) // only 2 columns now since size is removed
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function () {
                                $tenant = Filament::getTenant();
                                return \App\Models\Item::whereHas('department', fn ($q) =>
                                    $q->where('departments.id', $tenant->id)
                                )->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->live()
                            ->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->addActionLabel('Add Item'),
            ]);
    }
}