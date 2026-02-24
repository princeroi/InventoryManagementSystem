<?php

namespace App\Filament\Resources\Issuances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use App\Models\ItemVariant;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use Filament\Facades\Filament;
use App\Models\IssuanceType;

class IssuanceForm
{
    /**
     * Always reads directly from the DB — no cache.
     * This ensures stock numbers are always accurate after every issuance.
     */
    private static function variantsForItem(?int $itemId): array
    {
        if (! $itemId) {
            return [];
        }

        return ItemVariant::where('item_id', $itemId)
            ->get(['id', 'item_id', 'size_label', 'quantity'])
            ->keyBy('size_label')
            ->toArray();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('issued_to')
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'issued'  => 'Issued',
                    ])
                    ->default('pending')
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $now = now()->toDateString();
                        match ($state) {
                            'pending'   => $set('pending_at', $now),
                            'issued'    => $set('issued_at', $now),
                            'returned'  => $set('returned_at', $now),
                            'cancelled' => $set('cancelled_at', $now),
                            default     => null,
                        };
                    })
                    ->required(),

                DatePicker::make('pending_at')
                    ->label('Pending Date')
                    ->default(now())
                    ->visible(fn (callable $get) => $get('status') === 'pending')
                    ->required(fn (callable $get) => $get('status') === 'pending'),

                DatePicker::make('issued_at')
                    ->label('Issued Date')
                    ->visible(fn (callable $get) => $get('status') === 'issued')
                    ->required(fn (callable $get) => $get('status') === 'issued'),

                DatePicker::make('returned_at')
                    ->label('Returned Date')
                    ->visible(fn (callable $get) => $get('status') === 'returned')
                    ->required(fn (callable $get) => $get('status') === 'returned'),

                DatePicker::make('cancelled_at')
                    ->label('Cancelled Date')
                    ->visible(fn (callable $get) => $get('status') === 'cancelled')
                    ->required(fn (callable $get) => $get('status') === 'cancelled'),

                Textarea::make('note')
                    ->label('Note')
                    ->nullable()
                    ->columnSpanFull(),

                Repeater::make('issuance_items')
                    ->label('Items')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function () {
                                $tenant = Filament::getTenant();
                                return \App\Models\Item::whereHas('department', fn ($q) =>
                                    $q->where('departments.id', $tenant->id)
                                )->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if (! $state) return;

                                $allItems = $get('../../issuance_items') ?? [];

                                $duplicates = array_filter($allItems, fn ($row) =>
                                    isset($row['item_id']) && $row['item_id'] == $state
                                );

                                if (count($duplicates) > 1) {
                                    $set('item_id', null);
                                    $set('sizes', []);

                                    Notification::make()
                                        ->title('Item already added')
                                        ->body('This item already exists. Please add more sizes to the existing row instead.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $set('sizes', [['size' => null, 'quantity' => null]]);
                            })
                            ->required()
                            ->columnSpanFull(),

                        Repeater::make('sizes')
                            ->label('Sizes & Quantities')
                            ->columnSpanFull()
                            ->hidden(fn (callable $get) => ! $get('item_id'))
                            ->columns(2)
                            ->schema([
                                Select::make('size')
                                    ->label('Size')
                                    ->options(function (callable $get) {
                                        $itemId   = $get('../../item_id');

                                        // Always live from DB — no cache
                                        $variants = self::variantsForItem($itemId);

                                        return collect($variants)
                                            ->mapWithKeys(fn ($v) => [
                                                $v['size_label'] => "{$v['size_label']} (stock: {$v['quantity']})"
                                            ])
                                            ->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        if (! $state) return;

                                        $sizes = $get('../../sizes') ?? [];

                                        $duplicateKeys = array_keys(array_filter($sizes, fn ($row) =>
                                            isset($row['size']) && $row['size'] === $state
                                        ));

                                        if (count($duplicateKeys) > 1) {
                                            $set('size', null);
                                            $set('quantity', null);

                                            Notification::make()
                                                ->title("Size '{$state}' is already selected")
                                                ->body('Please update the quantity on the existing row instead.')
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->required(),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(debounce: 500)
                                    ->required(),

                                Placeholder::make('stock_note')
                                    ->label('')
                                    ->columnSpanFull()
                                    ->content(function (callable $get): ?HtmlString {
                                        $itemId   = $get('../../item_id');
                                        $size     = $get('size');
                                        $quantity = (int) $get('quantity');
                                        $status   = $get('../../../../status');

                                        if (! $itemId || ! $size) return null;

                                        $variants = self::variantsForItem($itemId);
                                        $stock    = $variants[$size]['quantity'] ?? 0;

                                        // Enough stock
                                        if ($quantity <= $stock || $quantity === 0) {
                                            return new HtmlString("
                                                <div style='
                                                    font-size:12px;
                                                    color:#059669;
                                                    background:#ecfdf5;
                                                    border:1px solid #a7f3d0;
                                                    border-radius:6px;
                                                    padding:6px 12px;
                                                    display:flex;
                                                    align-items:center;
                                                    gap:6px;
                                                '>
                                                    ✅ Stock available: <strong>{$stock}</strong>
                                                </div>
                                            ");
                                        }

                                        // Insufficient — hard block for stock-consuming status
                                        $isHard = $status === 'issued';

                                        if ($isHard) {
                                            return new HtmlString("
                                                <div style='
                                                    font-size:12px;
                                                    color:#dc2626;
                                                    background:#fef2f2;
                                                    border:1px solid #fecaca;
                                                    border-radius:6px;
                                                    padding:6px 12px;
                                                    display:flex;
                                                    align-items:center;
                                                    gap:6px;
                                                '>
                                                    🚫 Insufficient stock: <strong>{$stock}</strong> available, <strong>{$quantity}</strong> requested.
                                                    Change status to <strong>Pending</strong> or reduce quantity.
                                                </div>
                                            ");
                                        }

                                        return new HtmlString("
                                            <div style='
                                                font-size:12px;
                                                color:#d97706;
                                                background:#fffbeb;
                                                border:1px solid #fde68a;
                                                border-radius:6px;
                                                padding:6px 12px;
                                                display:flex;
                                                align-items:center;
                                                gap:6px;
                                            '>
                                                ⚠️ Stock is <strong>{$stock}</strong>, requested <strong>{$quantity}</strong>.
                                                You can save as <strong>Pending</strong> only.
                                            </div>
                                        ");
                                    }),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Size'),
                    ])
                    ->addActionLabel('Add Item')
                    ->required(),
            ]);
    }
}