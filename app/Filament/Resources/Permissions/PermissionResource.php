<?php

namespace App\Filament\Resources\Permissions;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource as BasePermissionResource;
use App\Filament\Resources\Permissions\Pages\ManagePermissions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use BackedEnum;

class PermissionResource extends BasePermissionResource
{
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationLabel               = 'Permissions';
    protected static ?int $navigationSort                   = 2;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        return $user->hasRole('super_admin') || $user->can('view-any permission');
    }

    public static function canCreate(): bool        { return Auth::user()?->can('create permission') ?? false; }
    public static function canEdit($r): bool        { return Auth::user()?->can('update permission') ?? false; }
    public static function canDelete($r): bool      { return Auth::user()?->can('delete permission') ?? false; }

    // -------------------------------------------------------------------------
    // Form Override — checkbox roles, and name field with category hint
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText(
                        'Use a prefix to auto-sort this permission into a category: ' .
                        '"uniform …", "sme …", "office supply …", or leave generic for General.'
                    ),

                TextInput::make('guard_name')
                    ->default('web')
                    ->required()
                    ->maxLength(255),

                Section::make('Assign to Roles')
                    ->collapsible()
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('')
                            ->options(
                                Role::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->relationship('roles', 'name')
                            ->bulkToggleable()
                            ->columns(2)
                            ->gridDirection('row'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePermissions::route('/'),
        ];
    }
}