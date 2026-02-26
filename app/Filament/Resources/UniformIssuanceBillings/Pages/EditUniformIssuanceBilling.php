<?php

namespace App\Filament\Resources\UniformIssuanceBillings\Pages;

use App\Filament\Resources\UniformIssuanceBillings\UniformIssuanceBillingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUniformIssuanceBilling extends EditRecord
{
    protected static string $resource = UniformIssuanceBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
