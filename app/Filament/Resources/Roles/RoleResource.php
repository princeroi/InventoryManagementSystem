<?php

namespace App\Filament\Resources\Roles;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource as BaseRoleResource;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class RoleResource extends BaseRoleResource
{

    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationLabel               = 'Roles';
    protected static ?int $navigationSort                   = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        return $user->hasRole('super_admin') || $user->can('view-any role');
    }

    public static function canCreate(): bool        { return Auth::user()?->can('create role') ?? false; }
    public static function canEdit($r): bool        { return Auth::user()?->can('update role') ?? false; }
    public static function canDelete($r): bool      { return Auth::user()?->can('delete role') ?? false; }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}