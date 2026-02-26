<?php

namespace App\Filament\Resources\UniformIssuanceBillings;

use App\Filament\Resources\UniformIssuanceBillings\Pages;
use App\Filament\Resources\UniformIssuanceBillings\Tables\UniformIssuanceBillingsTable;
use App\Models\UniformIssuanceBilling;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UniformIssuanceBillingResource extends Resource
{
    protected static ?string $model = UniformIssuanceBilling::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|null $navigationLabel = 'Billing';

    protected static int|null $navigationSort = 5;

    // Tenant scoping is handled manually in getTableQuery() on the List page
    // because billing records belong to issuances, not directly to a tenant.
    protected static bool $isScopedToTenant = false;

    // ── Tenant guard ──────────────────────────────────────────────────────
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
        return static::isHrTenant() && static::userCan('view-any uniform-issuance-billing');
    }

    // ── CRUD permissions ──────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return static::isHrTenant() && static::userCan('view-any uniform-issuance-billing');
    }

    public static function canView($record): bool
    {
        return static::isHrTenant() && static::userCan('view uniform-issuance-billing');
    }

    public static function canCreate(): bool
    {
        return static::isHrTenant() && static::userCan('create uniform-issuance-billing');
    }

    public static function canEdit($record): bool
    {
        return static::isHrTenant() && static::userCan('update uniform-issuance-billing');
    }

    public static function canDelete($record): bool
    {
        return static::isHrTenant() && static::userCan('delete uniform-issuance-billing');
    }

    public static function canDeleteAny(): bool
    {
        return static::isHrTenant() && static::userCan('delete-any uniform-issuance-billing');
    }

    // ── Navigation ────────────────────────────────────────────────────────
    public static function getNavigationGroup(): ?string
    {
        return 'Issuance / Deliveries';
    }

    // ── Form / Table ──────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return UniformIssuanceBillingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUniformIssuanceBillings::route('/'),
        ];
    }
}