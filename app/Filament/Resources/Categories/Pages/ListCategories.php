<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;  
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    private function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Category')
                ->visible(fn () => $this->userCan('create category'))
                ->mutateFormDataUsing(function (array $data): array {  // ← ADD THIS
                    $data['department_id'] = Filament::getTenant()->id;
                    return $data;
                }),
        ];
    }
}