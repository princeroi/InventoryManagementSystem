<?php

namespace App\Filament\Resources\UniformIssuanceBillings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UniformIssuanceBillingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('uniform_issuance_id')
                    ->relationship('uniformIssuance', 'id')
                    ->required(),
                TextInput::make('uniform_issuance_recipient_id')
                    ->required()
                    ->numeric(),
                TextInput::make('employee_name')
                    ->required(),
                TextInput::make('employee_status')
                    ->required(),
                TextInput::make('issuance_type')
                    ->required(),
                TextInput::make('bill_status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('endorsed_at'),
                DateTimePicker::make('billed_at'),
                TextInput::make('endorsed_by'),
                TextInput::make('billed_by'),
            ]);
    }
}
