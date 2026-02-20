<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    private function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

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
                ->visible(fn () => $this->userCan('create item'))
                ->label('Add Item')
                ->modalWidth('5xl')
                ->modalHeading('Add New Item'),
        ];
    }

}