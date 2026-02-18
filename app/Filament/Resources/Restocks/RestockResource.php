<?php

namespace App\Filament\Resources\Restocks;

use App\Filament\Resources\Restocks\Pages\ListRestocks;
use App\Filament\Resources\Restocks\Schemas\RestockForm;
use App\Filament\Resources\Restocks\Tables\RestocksTable;
use App\Models\Restock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RestockResource extends Resource
{
    protected static ?string $model = Restock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownOnSquare;

    public static function getNavigationGroup(): ?string
    {
        return 'Issuance / Deliveries';
    }

    public static function form(Schema $schema): Schema
    {
        return RestockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'items.item:id,name',
                'logs',
            ]);
    }
}