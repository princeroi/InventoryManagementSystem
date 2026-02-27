<?php

namespace App\Filament\Resources\OfficeSupplyRequests\Pages;

use App\Filament\Resources\OfficeSupplyRequests\OfficeSupplyRequestResource;
use App\Notifications\OfficeSupplyRequestedNotification;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListOfficeSupplyRequests extends ListRecords
{
    protected static string $resource = OfficeSupplyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => static::$resource::canCreate())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['department_id'] = Filament::getTenant()?->id;
                    $data['requested_by']  = Auth::id();
                    return $data;
                })
                ->after(function ($record) {
                    $record->load('employee', 'items');

                    User::officeSupplyApprovers()
                        ->each
                        ->notify(new OfficeSupplyRequestedNotification($record));
                }),
        ];
    }
}