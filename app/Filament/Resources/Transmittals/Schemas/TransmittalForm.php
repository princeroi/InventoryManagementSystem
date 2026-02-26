<?php

namespace App\Filament\Resources\Transmittals\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransmittalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transmittal_number')
                    ->label('Transmittal #')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('department_id')
                    ->label('Department')
                    ->relationship(
                        name: 'department',
                        titleAttribute: 'name',
                        // Scope departments to the current tenant
                        modifyQueryUsing: fn ($query) => $query->when(
                            Filament::getTenant(),
                            fn ($q) => $q->where('team_id', Filament::getTenant()->getKey())
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('transmitted_by')
                    ->label('Transmitted By')
                    ->required()
                    ->maxLength(255),

                Select::make('transmitted_by_user_id')
                    ->label('Transmitted By (User)')
                    ->relationship(
                        name: 'transmittedByUser',
                        titleAttribute: 'name',
                        // Scope users to the current tenant
                        modifyQueryUsing: fn ($query) => $query->when(
                            Filament::getTenant(),
                            fn ($q) => $q->whereHas('teams', fn ($q2) =>
                                $q2->where('teams.id', Filament::getTenant()->getKey())
                            )
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('transmitted_to')
                    ->label('Transmitted To')
                    ->required()
                    ->maxLength(255),

                Textarea::make('items_summary')
                    ->label('Items Summary')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}