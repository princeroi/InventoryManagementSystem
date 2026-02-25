<?php

namespace App\Filament\Resources\Transmittals;

use App\Filament\Resources\Transmittals\Pages\CreateTransmittal;
use App\Filament\Resources\Transmittals\Pages\EditTransmittal;
use App\Filament\Resources\Transmittals\Pages\ListTransmittals;
use App\Filament\Resources\Transmittals\Schemas\TransmittalForm;
use App\Filament\Resources\Transmittals\Tables\TransmittalsTable;
use App\Models\Transmittal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class TransmittalResource extends Resource
{
    protected static ?string $model = Transmittal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransmittals::route('/'),
            'create' => CreateTransmittal::route('/create'),
            'edit' => EditTransmittal::route('/{record}/edit'),
        ];
    }
}
