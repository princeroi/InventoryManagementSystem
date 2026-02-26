<?php

namespace App\Filament\Resources\Transmittals;

use App\Filament\Resources\Transmittals\Pages\CreateTransmittal;
use App\Filament\Resources\Transmittals\Pages\EditTransmittal;
use App\Filament\Resources\Transmittals\Pages\ListTransmittals;
use App\Filament\Resources\Transmittals\Schemas\TransmittalForm;
use App\Filament\Resources\Transmittals\Tables\TransmittalsTable;
use App\Models\Transmittal;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransmittalResource extends Resource
{
    protected static ?string $model = Transmittal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ── Tenant guard ──────────────────────────────────────────────────────
    //  Only expose this resource inside the "hr" tenant panel.
    private static function isHrTenant(): bool
    {
        return Filament::getTenant()?->slug === 'hr';
    }

    // ── Spatie permission helper ──────────────────────────────────────────
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Navigation visibility ─────────────────────────────────────────────
    public static function canAccess(): bool
    {
        return static::isHrTenant() && static::userCan('view-any transmittal');
    }

    // ── CRUD permissions (Spatie) ─────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return static::isHrTenant() && static::userCan('view-any transmittal');
    }

    public static function canView($record): bool
    {
        return static::isHrTenant() && static::userCan('view transmittal');
    }

    public static function canCreate(): bool
    {
        return static::isHrTenant() && static::userCan('create transmittal');
    }

    public static function canEdit($record): bool
    {
        return static::isHrTenant() && static::userCan('update transmittal');
    }

    public static function canDelete($record): bool
    {
        return static::isHrTenant() && static::userCan('delete transmittal');
    }

    public static function canDeleteAny(): bool
    {
        return static::isHrTenant() && static::userCan('delete-any transmittal');
    }

    // ── Navigation ────────────────────────────────────────────────────────
    public static function getNavigationGroup(): ?string
    {
        return 'Issuance / Deliveries';
    }

    // ── Tenant-scoped Eloquent query ──────────────────────────────────────
    //  Ensures records are always filtered to the current tenant.
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $tenant = Filament::getTenant();

        // Assumes Transmittal has a `team_id` / `tenant_id` column.
        // Adjust the column name to match your actual schema.
        if ($tenant) {
            $query->where('team_id', $tenant->getKey());
        }

        return $query;
    }

    // ── Form / Table ──────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return TransmittalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransmittalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTransmittals::route('/'),
        ];
    }
}