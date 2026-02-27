<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Employees';

    protected static ?int $navigationSort = 2;

    // ✅ Tenant scoping
    protected static bool $isScopedToTenant = true;

    // ── Spatie permission helper ──────────────────────────────────────────
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Navigation visibility ─────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return static::userCan('view-any employee');
    }

    // ── CRUD permissions ──────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return static::userCan('view-any employee');
    }

    public static function canView($record): bool
    {
        return static::userCan('view employee');
    }

    public static function canCreate(): bool
    {
        return static::userCan('create employee');
    }

    public static function canEdit($record): bool
    {
        return static::userCan('update employee');
    }

    public static function canDelete($record): bool
    {
        return static::userCan('delete employee');
    }

    public static function canDeleteAny(): bool
    {
        return static::userCan('delete-any employee');
    }

    // ── Navigation ────────────────────────────────────────────────────────
    public static function getNavigationGroup(): ?string
    {
        return 'Management';
    }

    // ── Tenant Query Scoping ──────────────────────────────────────────────
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Get current tenant (department)
        $tenant = Filament::getTenant();

        if ($tenant) {
            $query->where('department_id', $tenant->id);
        }

        return $query;
    }

    // ── Form / Table ──────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
        ];
    }
}