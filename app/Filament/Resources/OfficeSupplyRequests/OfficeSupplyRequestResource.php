<?php

namespace App\Filament\Resources\OfficeSupplyRequests;

use App\Filament\Resources\OfficeSupplyRequests\Pages\ListOfficeSupplyRequests;
use App\Filament\Resources\OfficeSupplyRequests\Schemas\OfficeSupplyRequestForm;
use App\Filament\Resources\OfficeSupplyRequests\Tables\OfficeSupplyRequestsTable;
use App\Models\OfficeSupplyRequest;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OfficeSupplyRequestResource extends Resource
{
    protected static ?string $model = OfficeSupplyRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Supply Requests';
    protected static ?string $slug            = 'office-supply-requests';
    protected static ?int    $navigationSort  = 2;

    // ── Tenant guard ──────────────────────────────────────────────────────
    public static function getNavigationGroup(): ?string
    {
        return 'Office Supplies';
    }
    

    private static function isOfficeSupplyTenant(): bool
    {
        return Filament::getTenant()?->slug === 'officesupply';
    }

    // ── Spatie permission helper ──────────────────────────────────────────

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Navigation visibility ─────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('view-any office-supply-request');
    }

    // ── CRUD permissions ──────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('view-any office-supply-request');
    }

    public static function canView($record): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('view office-supply-request');
    }

    public static function canCreate(): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('create office-supply-request');
    }

    public static function canEdit($record): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('update office-supply-request');
    }

    public static function canDelete($record): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('delete office-supply-request');
    }

    public static function canDeleteAny(): bool
    {
        return static::isOfficeSupplyTenant()
            && static::userCan('delete-any office-supply-request');
    }

    // ── Eloquent query (tenant scoped) ────────────────────────────────────

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('department_id', Filament::getTenant()?->id)
            ->with([
                'employee:id,name',
                'requestedBy:id,name',
                'items.item:id,name',
                'items.variant:id,size_label',
            ])
            ->latest('request_date');
    }

    // ── Form / Table ──────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return OfficeSupplyRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeSupplyRequestsTable::configure($table);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => ListOfficeSupplyRequests::route('/'),
        ];
    }
}