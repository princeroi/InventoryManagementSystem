<?php

namespace App\Filament\Resources\ItemVariants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('item_id')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('size_label')
                    ->required(),

                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}