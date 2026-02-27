<?php

namespace App\Filament\Resources\OfficeSupplyRequests\Schemas;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OfficeSupplyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Request Info ──────────────────────────────────────────────
            Section::make('Request Info')
                ->columns(2)
                ->schema([

                    Select::make('employee_id')
                        ->label('Requested For')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(1),

                    DatePicker::make('request_date')
                        ->label('Request Date')
                        ->default(now())
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Select::make('status')
                        ->options([
                            'requested' => 'Requested',
                            'completed' => 'Completed',
                        ])
                        ->default('requested')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),

                    Textarea::make('note')
                        ->rows(3)
                        ->columnSpanFull(),

                ]),

            // ── Items ─────────────────────────────────────────────────────
            Section::make('Items')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label('')
                        ->addActionLabel('Add Item')
                        ->minItems(1)
                        ->columns(3)
                        ->schema([

                            // Item select — scoped to tenant department
                            Select::make('item_id')
                                ->label('Item')
                                ->options(function () {
                                    $tenantId = Filament::getTenant()?->id;

                                    return Item::whereHas('departments', fn ($q) =>
                                            $q->where('departments.id', $tenantId)
                                        )
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    // Reset variant when item changes
                                    $set('item_variant_id', null);
                                })
                                ->columnSpan(1),

                            // Variant select — filtered by selected item, shows stock hint
                            Select::make('item_variant_id')
                                ->label('Variant / Size')
                                ->options(function (Get $get) {
                                    $itemId = $get('item_id');

                                    if (! $itemId) {
                                        return [];
                                    }

                                    return ItemVariant::where('item_id', $itemId)
                                        ->get()
                                        ->mapWithKeys(fn ($v) =>
                                            [$v->id => $v->size_label . ' — ' . $v->quantity . ' left']
                                        );
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->disabled(fn (Get $get) => ! $get('item_id'))
                                ->placeholder(fn (Get $get) => ! $get('item_id')
                                    ? 'Select an item first'
                                    : 'Select variant'
                                )
                                ->columnSpan(1),

                            // Quantity
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->suffix(fn (Get $get) => static::stockSuffix($get('item_variant_id')))
                                ->columnSpan(1),

                        ])
                        ->itemLabel(fn (array $state): ?string =>
                            $state['item_id']
                                ? (Item::find($state['item_id'])?->name ?? 'Item')
                                : 'New Item'
                        )
                        ->collapsible()
                        ->collapsed(false)
                        ->reorderable()
                        ->cloneable(),
                ]),

        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Returns a stock hint suffix for the quantity field.
     * e.g. "/ 12 in stock"
     */
    private static function stockSuffix(?int $variantId): ?string
    {
        if (! $variantId) {
            return null;
        }

        $stock = ItemVariant::find($variantId)?->quantity;

        if ($stock === null) {
            return null;
        }

        return '/ ' . $stock . ' in stock';
    }
}