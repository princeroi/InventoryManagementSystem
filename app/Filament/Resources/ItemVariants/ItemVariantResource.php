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

class ItemVariantResource extends Resource
{
    protected static ?string $model = ItemVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    // Removed $recordTitleAttribute = 'Stock' — not a real model column

    protected static ?string $navigationLabel = 'Stocks';

    protected static ?string $modelLabel = 'Stocks';

    protected static ?string $pluralModelLabel = 'Stocks';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Stock Management';
    }

    public static function form(Schema $schema): Schema
    {
        return ItemVariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemVariantsTable::configure($table);
    }

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

    public static function getEloquentQuery(): Builder
    {
        // Eager load item so item.name column doesn't fire N+1 per row
        return parent::getEloquentQuery()
            ->with(['item:id,name']);
    }
}