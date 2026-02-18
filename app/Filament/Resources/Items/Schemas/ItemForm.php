<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required(),

                // Fixed: relationship name should be lowercase 'category' not 'Category'
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2),
                    ]),

                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),

                Repeater::make('itemVariants')
                    ->relationship()
                    ->label('')
                    ->schema([
                        TextInput::make('size_label')
                            ->required()
                            ->placeholder('e.g. S, M, L, 500ml, EU 42...')
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->addActionLabel('+ Add Variant')
                    ->reorderable()
                    ->cloneable()
                    ->collapsible()
                    ->defaultItems(1)
                    ->columnSpanFull(),
            ]);
    }
}