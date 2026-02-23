<?php

namespace App\Filament\Resources\UniformSets;

use App\Filament\Resources\UniformSets\Pages\CreateUniformSet;
use App\Filament\Resources\UniformSets\Pages\EditUniformSet;
use App\Filament\Resources\UniformSets\Pages\ListUniformSets;
use App\Filament\Resources\UniformSets\Schemas\UniformSetForm;
use App\Filament\Resources\UniformSets\Tables\UniformSetsTable;
use App\Models\UniformSet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class UniformSetResource extends Resource
{
    public static function canAccess(): bool
    {
        return Filament::getTenant()?->slug === 'hr';
    }

     public static function getNavigationGroup(): ?string
    {
        return 'HR Settings';
    }

    protected static ?string $model = UniformSet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UniformSetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UniformSetsTable::configure($table);
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
            'index' => ListUniformSets::route('/'),
        ];
    }
}
