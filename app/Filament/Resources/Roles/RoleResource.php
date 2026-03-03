<?php

namespace App\Filament\Resources\Roles;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource as BaseRoleResource;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
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

    public static function canCreate(): bool   { return Auth::user()?->can('create role') ?? false; }
    public static function canEdit($r): bool   { return Auth::user()?->can('update role') ?? false; }
    public static function canDelete($r): bool { return Auth::user()?->can('delete role') ?? false; }

    // -------------------------------------------------------------------------
    // Permission groups — hardcoded DB IDs, spec table order
    // -------------------------------------------------------------------------

    public const PERMISSION_GROUPS = [

        'Uniform Inventory' => [
            'icon'        => 'heroicon-o-archive-box',
            'badge_color' => 'info',
            'description' => 'Uniforms · Stock · Restocks · Sites · Categories',
            'ids' => [
                57, 16, 30, 22, 10, 18, 26, 51,
                32, 24, 13, 20, 28, 53, 14, 55,
                54, 15, 56, 31, 23, 11, 19, 27,
                52, 76, 77, 78, 75, 29, 21,  9,
                17, 25, 50,
            ],
        ],

        'SME Inventory' => [
            'icon'        => 'heroicon-o-cube',
            'badge_color' => 'success',
            'description' => 'Issuances · Stock · Restocks · Sites · Categories',
            'ids' => [
                 8, 16, 30,  2, 22, 10, 18, 26,
                32,  4, 24, 13, 20, 28, 14,  6,
                 5,  7, 15, 31,  3, 23, 11, 19,
                27, 76, 77, 78, 75, 29,  1, 21,
                 9, 17, 25,
            ],
        ],

        'Office Supply Inventory' => [
            'icon'        => 'heroicon-o-shopping-bag',
            'badge_color' => 'warning',
            'description' => 'Office Supplies · Requests · Restocks · Stock',
            'ids' => [
                59, 70, 61, 72, 22, 10, 16, 30,
                26, 32, 24, 13, 28, 73, 14, 74,
                31, 15, 23, 60, 71, 11, 27, 76,
                69, 77, 78, 75, 29, 21, 58, 68,
                 9, 25,
            ],
        ],

        'Super Admin' => [
            'icon'        => 'heroicon-o-shield-check',
            'badge_color' => 'danger',
            'description' => 'Users · Roles · Permissions · Employees · Config',
            'ids' => [
                64, 46, 38, 42, 34, 66, 48, 40,
                44, 36, 67, 65, 47, 39, 43, 35,
                63, 62, 45, 37, 41, 33,
            ],
        ],
    ];

    // -------------------------------------------------------------------------
    // Table — preserve parent columns, replace actions with custom EditAction
    // that wires ->using() so permissions are synced on save.
    //
    // Uses \Filament\Actions\EditAction (confirmed from base package source).
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                \Filament\Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                // ── EditAction with custom ->using() to sync permissions ──────
                EditAction::make()
                    ->using(function ($record, array $data): mixed {
                        $permissionIds = ManageRoles::extractPermissionIdsFromData($data);

                        ManageRoles::validateUniqueRoleName(
                            name:      $data['name'],
                            guard:     $data['guard_name'] ?? 'web',
                            excludeId: $record->id,
                        );

                        $record->update($data);

                        $record->syncPermissions(
                            Permission::whereIn('id', $permissionIds)->get()
                        );

                        return $record;
                    }),

                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // -------------------------------------------------------------------------
    // Form
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Role Identity ─────────────────────────────────────────────────
            Section::make()
                ->schema([
                    Placeholder::make('role_identity_header')
                        ->label('')
                        ->content(new HtmlString('
                            <div style="display:flex;align-items:center;gap:12px;padding-bottom:4px;">
                                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px #6366f140;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                                </div>
                                <div>
                                    <p style="font-size:17px;font-weight:700;color:#111827;margin:0;line-height:1.3;">Role Configuration</p>
                                    <p style="font-size:12px;color:#6b7280;margin:0;">Define identity and assign granular permissions</p>
                                </div>
                            </div>
                        ')),

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->placeholder('e.g. inventory-manager')
                            ->prefixIcon('heroicon-o-tag')
                            ->helperText('Use lowercase with hyphens. E.g. warehouse-staff')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->helperText('Authentication guard this role belongs to.')
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),

            // ── Master Permission Toggle ──────────────────────────────────────
            Section::make()
                ->schema([
                    Placeholder::make('master_toggle_header')
                        ->label('')
                        ->content(new HtmlString('
                            <div style="display:flex;align-items:center;gap:10px;padding-bottom:2px;">
                                <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 10px #ef444440;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" style="width:17px;height:17px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                </div>
                                <div>
                                    <p style="font-size:15px;font-weight:600;color:#111827;margin:0;">Master Permission Control</p>
                                    <p style="font-size:12px;color:#6b7280;margin:0;">Grant or revoke all permissions across every group instantly</p>
                                </div>
                            </div>
                        ')),

                    Toggle::make('select_all_permissions')
                        ->label('Select All Permissions')
                        ->helperText('Flips every checkbox in every group below simultaneously.')
                        ->onColor('danger')
                        ->offColor('gray')
                        ->onIcon('heroicon-m-check-badge')
                        ->offIcon('heroicon-m-x-circle')
                        ->live()
                        ->afterStateHydrated(function (Toggle $component, $record): void {
                            if (! $record) {
                                $component->state(false);
                                return;
                            }
                            $savedCount = $record->permissions()->count();
                            $totalCount = Permission::count();
                            $component->state($savedCount > 0 && $savedCount === $totalCount);
                        })
                        ->afterStateUpdated(function (bool $state, Set $set): void {
                            foreach (self::PERMISSION_GROUPS as $label => $config) {
                                $slug = Str::slug($label);
                                $set('group_' . $slug, $state ? $config['ids'] : []);
                                $set('select_all_' . $slug, $state);
                            }
                        })
                        ->dehydrated(false),
                ]),

            // ── Permission Groups ─────────────────────────────────────────────
            ...self::buildPermissionSections(),
        ]);
    }

    private static function buildPermissionSections(): array
    {
        $allNeededIds = collect(self::PERMISSION_GROUPS)
            ->flatMap(fn ($g) => $g['ids'])
            ->unique()
            ->values()
            ->all();

        $permissionsById = Permission::whereIn('id', $allNeededIds)
            ->get()
            ->keyBy('id');

        $badgeStyles = [
            'info'    => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'dot' => '#3b82f6', 'border' => '#bfdbfe'],
            'success' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'dot' => '#22c55e', 'border' => '#bbf7d0'],
            'warning' => ['bg' => '#fffbeb', 'text' => '#b45309', 'dot' => '#f59e0b', 'border' => '#fde68a'],
            'danger'  => ['bg' => '#fff1f2', 'text' => '#be123c', 'dot' => '#f43f5e', 'border' => '#fecdd3'],
        ];

        $sections = [];

        foreach (self::PERMISSION_GROUPS as $label => $config) {
            $options = [];
            foreach ($config['ids'] as $id) {
                $perm = $permissionsById[$id] ?? null;
                if ($perm !== null) {
                    $options[$perm->id] = $perm->name;
                }
            }

            if (empty($options)) {
                continue;
            }

            $fieldKey  = 'group_' . Str::slug($label);
            $groupIds  = array_keys($options);
            $count     = count($options);
            $bs        = $badgeStyles[$config['badge_color']] ?? $badgeStyles['info'];
            $slugLabel = Str::slug($label);

            $headerHtml = '
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding-bottom:2px;">
                    <span style="
                        display:inline-flex;align-items:center;gap:6px;
                        padding:4px 12px 4px 9px;border-radius:20px;
                        background:' . $bs['bg'] . ';
                        border:1px solid ' . $bs['border'] . ';
                        font-size:12px;font-weight:700;color:' . $bs['text'] . ';
                        letter-spacing:0.02em;
                    ">
                        <span style="width:7px;height:7px;border-radius:50%;background:' . $bs['dot'] . ';flex-shrink:0;"></span>
                        ' . $label . '
                    </span>
                    <span style="
                        padding:3px 10px;border-radius:20px;
                        background:#f3f4f6;color:#6b7280;
                        font-size:11px;font-weight:600;letter-spacing:0.04em;
                    ">' . $count . ' permissions</span>
                </div>
                <p style="font-size:12px;color:#9ca3af;margin:4px 0 0 0;padding-bottom:4px;">' . $config['description'] . '</p>
            ';

            $sections[] = Section::make()
                ->collapsible()
                ->collapsed(false)
                ->schema([
                    Placeholder::make('group_header_' . $slugLabel)
                        ->label('')
                        ->content(new HtmlString($headerHtml)),

                    Toggle::make('select_all_' . $slugLabel)
                        ->label('Select All — ' . $label)
                        ->helperText('Toggle all ' . $count . ' permissions in this group.')
                        ->onColor('success')
                        ->offColor('gray')
                        ->onIcon('heroicon-m-check-badge')
                        ->offIcon('heroicon-m-x-circle')
                        ->live()
                        ->afterStateHydrated(function (Toggle $component, $record) use ($groupIds): void {
                            if (! $record) {
                                $component->state(false);
                                return;
                            }
                            $savedIds    = $record->permissions()->pluck('id')->toArray();
                            $allSelected = ! empty($groupIds)
                                && count(array_intersect($groupIds, $savedIds)) === count($groupIds);
                            $component->state($allSelected);
                        })
                        ->afterStateUpdated(function (bool $state, Set $set) use ($fieldKey, $groupIds): void {
                            $set($fieldKey, $state ? $groupIds : []);
                        })
                        ->dehydrated(false),

                    CheckboxList::make($fieldKey)
                        ->label('')
                        ->options($options)
                        ->afterStateHydrated(function (CheckboxList $component, $record): void {
                            if (! $record) {
                                $component->state([]);
                                return;
                            }
                            $savedIds    = $record->permissions()->pluck('id')->toArray();
                            $groupOptIds = array_keys($component->getOptions());
                            $component->state(array_values(array_intersect($savedIds, $groupOptIds)));
                        })
                        ->live()
                        ->afterStateUpdated(function (array $state, Set $set) use ($groupIds, $slugLabel): void {
                            $set('select_all_' . $slugLabel, count($state) === count($groupIds));
                        })
                        ->bulkToggleable()
                        ->columns(3)
                        ->gridDirection('row')
                        ->dehydrated(true),
                ]);
        }

        return $sections;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}