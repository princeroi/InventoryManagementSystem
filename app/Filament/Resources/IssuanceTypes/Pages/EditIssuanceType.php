<?php

namespace App\Filament\Resources\IssuanceTypes\Pages;

use App\Filament\Resources\IssuanceTypes\IssuanceTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIssuanceType extends EditRecord
{
    protected static string $resource = IssuanceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
