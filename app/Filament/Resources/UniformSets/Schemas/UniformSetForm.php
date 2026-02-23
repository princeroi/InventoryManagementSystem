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
                    ->columns(3)
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
                            ->afterStateUpdated(fn (callable $set) => $set('size', null))
                            ->required(),

                        Select::make('size')
                            ->label('Size')
                            ->options(function (callable $get) {
                                $itemId = $get('item_id');
                                if (! $itemId) return [];
                                return ItemVariant::where('item_id', $itemId)
                                    ->pluck('size_label', 'size_label')
                                    ->toArray();
                            })
                            ->live()
                            ->nullable(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->addActionLabel('Add Item'),
            ]);
    }
}
