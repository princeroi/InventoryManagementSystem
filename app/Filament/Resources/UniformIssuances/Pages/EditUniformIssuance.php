<?php

namespace App\Filament\Resources\UniformIssuances\Pages;

use App\Filament\Resources\UniformIssuances\UniformIssuanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUniformIssuance extends EditRecord
{
    protected static string $resource = UniformIssuanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
