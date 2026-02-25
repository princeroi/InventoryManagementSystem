<?php

namespace App\Filament\Resources\Transmittals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransmittalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transmittal_number')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                TextInput::make('transmitted_by')
                    ->required(),
                Select::make('transmitted_by_user_id')
                    ->relationship('transmittedByUser', 'name'),
                TextInput::make('transmitted_to')
                    ->required(),
                TextInput::make('items_summary'),
            ]);
    }
}
