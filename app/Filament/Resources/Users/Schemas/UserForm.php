<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MultiSelect;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->getRawOriginal('password')))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn ($record): bool => $record === null),
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name'),
                MultiSelect::make('departments')
                    ->relationship('departments', 'name')
                    ->label('Departments'),
            ]);
    }
}