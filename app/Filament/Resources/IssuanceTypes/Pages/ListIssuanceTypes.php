<?php

namespace App\Filament\Resources\IssuanceTypes\Pages;

use App\Filament\Resources\IssuanceTypes\IssuanceTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuanceTypes extends ListRecords
{
    protected static string $resource = IssuanceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
