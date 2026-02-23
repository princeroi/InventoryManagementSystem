<?php

namespace App\Filament\Resources\UniformSets\Pages;

use App\Filament\Resources\UniformSets\UniformSetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUniformSet extends EditRecord
{
    protected static string $resource = UniformSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
