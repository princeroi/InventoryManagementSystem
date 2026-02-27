<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class ManageRoles extends ManageRecords
{
    protected static string $resource = RoleResource::class;

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('view-any role'), 403);
        parent::mount();
    }

    private function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => $this->userCan('create role'))
                ->using(function (array $data, string $model): mixed {
                    $permissionIds = array_map('intval', (array) ($data['permissions'] ?? []));
                    unset($data['permissions']);

                    $record = $model::create($data);

                    if (! empty($permissionIds)) {
                        $record->syncPermissions(
                            Permission::whereIn('id', $permissionIds)->get()
                        );
                    }

                    return $record;
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $permissionIds = array_map('intval', (array) ($data['permissions'] ?? []));
        unset($data['permissions']);

        $record->update($data);

        $record->syncPermissions(
            Permission::whereIn('id', $permissionIds)->get()
        );

        return $record;
    }
}