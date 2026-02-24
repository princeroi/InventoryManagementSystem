<?php

namespace App\Filament\Resources\UniformIssuances;

use App\Filament\Resources\UniformIssuances\Pages\CreateUniformIssuance;
use App\Filament\Resources\UniformIssuances\Pages\EditUniformIssuance;
use App\Filament\Resources\UniformIssuances\Pages\ListUniformIssuances;
use App\Filament\Resources\UniformIssuances\Schemas\UniformIssuanceForm;
use App\Filament\Resources\UniformIssuances\Tables\UniformIssuancesTable;
use App\Models\UniformIssuance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth; // ← ADD THIS

class UniformIssuanceResource extends Resource
{
    // ── Permission helper ─────────────────────────────────────────────────
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Access & CRUD permissions ─────────────────────────────────────────
    public static function canAccess(): bool
    {
        return Filament::getTenant()?->slug === 'hr'
            && self::userCan('view-any uniform-issuance'); // ← ADD PERMISSION CHECK
    }

    public static function canViewAny(): bool       { return self::userCan('view-any uniform-issuance'); }
    public static function canCreate(): bool        { return self::userCan('create uniform-issuance'); }
    public static function canEdit($record): bool   { return self::userCan('update uniform-issuance'); }
    public static function canDelete($record): bool { return self::userCan('delete uniform-issuance'); }

    public static function getNavigationGroup(): ?string
    {
        return 'Issuance / Deliveries';
    }

    protected static ?string $model = UniformIssuance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UniformIssuanceForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return UniformIssuancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUniformIssuances::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'site:id,name',
            'issuanceType:id,name',
            'recipients.position:id,name',
            'recipients.items.item:id,name',
            'logs',
        ]);
    }
}