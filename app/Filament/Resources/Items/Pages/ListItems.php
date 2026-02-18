<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getTableFilters(): array
    {
        $categories = Category::orderBy('name')
            ->pluck('name', 'id');

        return [
            SelectFilter::make('category_id')
                ->label('Category')
                ->options($categories)
                ->placeholder('All Items')
                ->query(fn ($query, $value) => $value ? $query->where('category_id', $value) : $query),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Item')
                ->modalWidth('5xl')
                ->modalHeading('Add New Item'),
        ];
    }

}