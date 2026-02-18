<?php

namespace App\Filament\Resources\Items;

use App\Filament\Resources\Items\Pages\CreateItem;
use App\Filament\Resources\Items\Pages\EditItem;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Items\Schemas\ItemForm;
use App\Filament\Resources\Items\Tables\ItemsTable;
use App\Models\Item;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 1;

    // Removed $recordTitleAttribute = 'Item' — not a real model column

    public static function getNavigationGroup(): ?string
    {
        return 'Stock Management';
    }

    public static function form(Schema $schema): Schema
    {
        return ItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListItems::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Eager load both relationships so the table doesn't N+1 per row.
        // Only fetch the columns we actually need from itemVariants.
        return parent::getEloquentQuery()
            ->with([
                'category:id,name',
                'itemVariants:id,item_id,size_label,quantity',
            ]);
    }
}