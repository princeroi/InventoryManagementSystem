<?php

namespace App\Filament\Resources\Transmittals\Pages;

use App\Filament\Resources\Transmittals\TransmittalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransmittal extends EditRecord
{
    protected static string $resource = TransmittalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
