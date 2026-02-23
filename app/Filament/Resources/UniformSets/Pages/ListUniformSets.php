<?php

namespace App\Filament\Resources\UniformSets\Pages;

use App\Filament\Resources\UniformSets\UniformSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUniformSets extends ListRecords
{
    protected static string $resource = UniformSetResource::class;

    protected function getHeaderActions(): array
    {
       return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['department_id'] = Filament::getTenant()->id;
                    return $data;
                }),
        ];
    }
}
