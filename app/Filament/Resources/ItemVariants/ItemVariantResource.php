<?php

namespace App\Filament\Resources\ItemVariants;

use App\Filament\Resources\ItemVariants\Pages\ListItemVariants;
use App\Filament\Resources\ItemVariants\Schemas\ItemVariantForm;
use App\Filament\Resources\ItemVariants\Tables\ItemVariantsTable;
use App\Models\ItemVariant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class ItemVariantResource extends Resource
{
    protected static bool $isScopedToTenant = false;
    protected static ?string $model = ItemVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    // Removed $recordTitleAttribute = 'Stock' — not a real model column

    protected static ?string $navigationLabel = 'Stocks';

    protected static ?string $modelLabel = 'Stocks';

    protected static ?string $pluralModelLabel = 'Stocks';

    protected static ?int $navigationSort = 2;

    // -------------------------------------------------------------------------
    // Permissions
    // -------------------------------------------------------------------------

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    public static function canViewAny(): bool       { return self::userCan('view-any stock'); }
    public static function canCreate(): bool        { return self::userCan('create stock'); }
    public static function canEdit($record): bool   { return self::userCan('update stock'); }
    public static function canDelete($record): bool { return self::userCan('delete stock'); }

    // -------------------------------------------------------------------------
    // Navigation
    // -------------------------------------------------------------------------

    public static function getNavigationGroup(): ?string
    {
        return 'Stock Management';
    }

    // -------------------------------------------------------------------------
    // Schema / Table
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return ItemVariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemVariantsTable::configure($table);
    }

    // -------------------------------------------------------------------------
    // Query
    // -------------------------------------------------------------------------

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['item:id,name,category_id']);

        $tenant = Filament::getTenant();

        if ($tenant) {
            // Filter variants whose item belongs to the current department
            $query->whereHas('item', function (Builder $q) use ($tenant) {
                $q->whereHas('department', function (Builder $q) use ($tenant) {
                    $q->where('departments.id', $tenant->id);
                });
            });
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Pages / Relations
    // -------------------------------------------------------------------------

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemVariants::route('/'),
        ];
    }
}