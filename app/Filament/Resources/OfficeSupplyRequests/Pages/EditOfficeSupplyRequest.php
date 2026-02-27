<?php

namespace App\Filament\Resources\OfficeSupplyRequests\Pages;

use App\Filament\Resources\OfficeSupplyRequests\OfficeSupplyRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficeSupplyRequest extends EditRecord
{
    protected static string $resource = OfficeSupplyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
