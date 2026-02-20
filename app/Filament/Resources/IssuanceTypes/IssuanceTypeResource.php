<?php

namespace App\Filament\Resources\IssuanceTypes;

use App\Filament\Resources\IssuanceTypes\Pages\ListIssuanceTypes;
use App\Models\IssuanceType;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Filament\Traits\RestrictedToDepartments;

class IssuanceTypeResource extends Resource
{
    use RestrictedToDepartments;

    protected static function allowedDepartments(): array
    {
        return ['hr']; 
    }
    protected static ?string $tenantOwnershipRelationshipName = 'department';
    protected static ?string $model = IssuanceType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Issuance Types';
    protected static ?string $modelLabel      = 'Issuance Type';
    protected static ?string $pluralModelLabel = 'Issuance Types';
    protected static ?int    $navigationSort  = 3;

    // -------------------------------------------------------------------------
    // Permissions
    // -------------------------------------------------------------------------

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    public static function canEdit($record): bool   { return self::userCan('update issuance-type'); }
    public static function canDelete($record): bool { return self::userCan('delete issuance-type'); }

    // -------------------------------------------------------------------------
    // Navigation
    // -------------------------------------------------------------------------

    public static function getNavigationGroup(): ?string
    {
        return 'Stock Management';
    }

    // -------------------------------------------------------------------------
    // Schema / Table
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('issuances_count')
                    ->label('Issuances')
                    ->counts('issuances')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => ListIssuanceTypes::route('/'),
        ];
    }
}