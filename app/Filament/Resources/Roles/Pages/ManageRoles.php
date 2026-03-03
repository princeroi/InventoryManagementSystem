<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

    // -------------------------------------------------------------------------
    // Collect permission IDs from all group_* fields at save time.
    // -------------------------------------------------------------------------

    public static function extractPermissionIdsFromData(array &$data): array
    {
        $ids = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'group_')) {
                $ids = array_merge($ids, array_map('intval', (array) $value));
                unset($data[$key]);
            }
        }

        unset($data['permissions']);

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'select_all_')) {
                unset($data[$key]);
            }
        }

        return array_values(array_unique($ids));
    }

    // -------------------------------------------------------------------------
    // Validate that no role with the same name + guard already exists.
    // -------------------------------------------------------------------------

    public static function validateUniqueRoleName(string $name, string $guard, ?int $excludeId = null): void
    {
        $exists = Role::where('name', $name)
            ->where('guard_name', $guard)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Role already exists')
                ->body("A role named \"{$name}\" already exists for guard \"{$guard}\". Please choose a different name.")
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.name' => "A role named \"{$name}\" already exists for guard \"{$guard}\".",
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Header actions (Create)
    // -------------------------------------------------------------------------

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => $this->userCan('create role'))
                ->using(function (array $data, string $model): mixed {
                    $permissionIds = self::extractPermissionIdsFromData($data);

                    self::validateUniqueRoleName(
                        name:  $data['name'],
                        guard: $data['guard_name'] ?? 'web',
                    );

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

    // -------------------------------------------------------------------------
    // handleRecordUpdate is kept here for reference but the real save on edit
    // is wired via RoleResource::table() EditAction ->using() callback below.
    // -------------------------------------------------------------------------

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $permissionIds = self::extractPermissionIdsFromData($data);

        self::validateUniqueRoleName(
            name:      $data['name'],
            guard:     $data['guard_name'] ?? 'web',
            excludeId: $record->id,
        );

        $record->update($data);

        $record->syncPermissions(
            Permission::whereIn('id', $permissionIds)->get()
        );

        return $record;
    }
}