<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        // ✅ Get current tenant (department)
        $tenant = Filament::getTenant();

        return $schema
            ->components([
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->required()
                    ->default($tenant?->id) // ✅ Auto-fill with current tenant
                    ->disabled(fn () => $tenant !== null) // ✅ Lock when in tenant context
                    ->dehydrated(), // ✅ Save value even when disabled

                TextInput::make('employee_id')
                    ->label('Employee ID')
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->placeholder('EMP-001'),

                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Juan Dela Cruz'),

                TextInput::make('position')
                    ->label('Position')
                    ->maxLength(255)
                    ->placeholder('Security Guard'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Inactive employees will not appear in dropdowns'),
            ]);
    }
}