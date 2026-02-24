<?php

namespace App\Filament\Resources\UniformIssuances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use App\Models\IssuanceType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Position;
use App\Models\UniformSet;
use App\Models\UniformSetItem;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UniformIssuanceForm
{
    private static function stockForVariant(?int $itemId, ?string $size): int
    {
        if (! $itemId || ! $size) return 0;

        return ItemVariant::where('item_id', $itemId)
            ->where('size_label', $size)
            ->value('quantity') ?? 0;
    }

    public static function make(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->required(),

                Select::make('issuance_type_id')
                    ->label('Issuance Type')
                    ->options(fn () => IssuanceType::where('department_id', Filament::getTenant()?->id)
                        ->pluck('name', 'id')
                        ->toArray())
                    ->required(),

                Select::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'released'  => 'Released',
                        'issued'    => 'Issued',
                    ])
                    ->default('pending')
                    ->live()
                    ->afterStateUpdated(function ($set, $state): void {
                        $now = now()->toDateString();
                        match ($state) {
                            'pending'   => $set('pending_at', $now),
                            'released'  => $set('released_at', $now),
                            'partial'   => $set('partial_at', $now),
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
                    ->visible(fn ($get) => $get('status') === 'pending')
                    ->required(fn ($get) => $get('status') === 'pending'),

                DatePicker::make('partial_at')
                    ->label('Partial Date')
                    ->visible(fn ($get) => $get('status') === 'partial')
                    ->required(fn ($get) => $get('status') === 'partial'),

                DatePicker::make('released_at')
                    ->label('Released Date')
                    ->visible(fn ($get) => $get('status') === 'released')
                    ->required(fn ($get) => $get('status') === 'released'),

                DatePicker::make('issued_at')
                    ->label('Issued Date')
                    ->visible(fn ($get) => $get('status') === 'issued')
                    ->required(fn ($get) => $get('status') === 'issued'),

                DatePicker::make('returned_at')
                    ->label('Returned Date')
                    ->visible(fn ($get) => $get('status') === 'returned')
                    ->required(fn ($get) => $get('status') === 'returned'),

                DatePicker::make('cancelled_at')
                    ->label('Cancelled Date')
                    ->visible(fn ($get) => $get('status') === 'cancelled')
                    ->required(fn ($get) => $get('status') === 'cancelled'),

                Repeater::make('employees')
                    ->columnSpanFull()
                    ->collapsible()
                    ->cloneable(false)
                    ->addActionLabel('Add Another Employee')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->itemLabel(fn (array $state): ?string => $state['employee_name'] ?? 'New Employee')
                    ->afterStateUpdated(function ($state, $set): void {
                        if (count($state) > 1) {
                            $lastIndex = count($state) - 1;
                            foreach ($state as $index => $employee) {
                                if ($index !== $lastIndex) {
                                    $set("employees.{$index}.collapsed", true);
                                }
                            }
                        }
                    })
                    ->extraAttributes(['class' => 'fi-repeater-without-controls'])
                    ->schema([
                        TextInput::make('employee_name')
                            ->label('Employee Name')
                            ->required()
                            ->live(debounce: '500ms')
                            ->columnSpan(1),

                        Select::make('position_id')
                            ->label('Position')
                            ->options(fn () => Position::where('department_id', Filament::getTenant()?->id)
                                ->pluck('name', 'id')
                                ->toArray())
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                $set('uniform_set_id', null);
                                $set('items', []);
                            })
                            ->required()
                            ->columnSpan(1),

                        Select::make('uniform_set_id')
                            ->label('Uniform Set')
                            ->options(function ($get) {
                                $positionId = $get('position_id');
                                $options = ['manual' => '✏️ Manual Input'];

                                if ($positionId) {
                                    $sets = UniformSet::where('department_id', Filament::getTenant()?->id)
                                        ->where('position_id', $positionId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                    $options += $sets;
                                }

                                return $options;
                            })
                            ->live()
                            ->afterStateUpdated(function ($get, $set, $state): void {
                                $set('items', []);

                                if ($state && $state !== 'manual') {
                                    $setItems = UniformSetItem::with('item')
                                        ->where('uniform_set_id', (int) $state)
                                        ->get();

                                    if ($setItems->isNotEmpty()) {
                                        $items = $setItems->map(fn ($si) => [
                                            'item_id'  => (string) $si->item_id,
                                            'size'     => $si->size,
                                            'quantity' => (int) ($si->quantity ?? 1),
                                        ])->all();

                                        $set('items', $items);
                                    }
                                }
                            })
                            ->required()
                            ->columnSpan(1),

                        Repeater::make('items')
                            ->collapsible()
                            ->collapsed(false)
                            ->itemLabel(fn (array $state): ?string =>
                                filled($state['item_id'])
                                    ? (Item::find($state['item_id'])?->name ?? 'Item') .
                                      (filled($state['quantity']) ? " × {$state['quantity']}" : '')
                                    : 'New Item'
                            )
                            ->afterStateUpdated(function ($state, $set, $operation, $get): void {
                                if ($operation === 'add') {
                                    $set('collapsed', true);
                                }

                                if ($operation === 'add' || $operation === 'edit') {
                                    $currentItemId = $get('item_id');
                                    $currentSize   = $get('size');

                                    if ($currentItemId) {
                                        $existing = collect($state)->filter(fn ($item) => $item['item_id'] === $currentItemId);

                                        if ($existing->count() > 1) {
                                            Notification::make()
                                                ->title('Item already exists')
                                                ->body('This item is already in the list. Increase quantity or use a different size.')
                                                ->warning()
                                                ->send();
                                        }

                                        $sameSize = $existing->filter(fn ($item) => $item['size'] === $currentSize);

                                        if ($sameSize->count() > 1) {
                                            Notification::make()
                                                ->title('Size already exists')
                                                ->body('This size is already added for this item. Increase quantity instead.')
                                                ->warning()
                                                ->send();
                                        }
                                    }
                                }
                            })
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(function () {
                                        $tenant = Filament::getTenant();
                                        return Item::whereHas('department', fn ($q) =>
                                            $q->where('id', $tenant?->id)
                                        )
                                        ->pluck('name', 'id')
                                        ->toArray();
                                    })
                                    ->getOptionLabelUsing(fn ($value) => Item::find($value)?->name ?? '—')
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(1),

                                Select::make('size')
                                    ->label('Size')
                                    ->options(function ($get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) return [];

                                        return ItemVariant::where('item_id', $itemId)
                                            ->get()
                                            ->mapWithKeys(fn ($variant) => [
                                                $variant->size_label => $variant->size_label . " (stock: {$variant->quantity})"
                                            ])
                                            ->all();
                                    })
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 500)
                                    ->columnSpan(1),

                                // ── Stock warning ─────────────────────────────
                                Placeholder::make('stock_warning')
                                    ->label('')
                                    ->columnSpanFull()
                                    ->content(function ($get): ?HtmlString {
                                        $itemId   = $get('item_id');
                                        $size     = $get('size');
                                        $quantity = (int) $get('quantity');
                                        $status   = $get('../../../../../../status'); // up through employees repeater

                                        if (! $itemId || ! $size) return null;

                                        $stock = self::stockForVariant((int) $itemId, $size);

                                        // Enough stock
                                        if ($quantity <= $stock) {
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

                                        // Insufficient stock
                                        $isHard = in_array($status, ['released', 'partial', 'issued']);

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
                            ->addActionLabel('Add Item')
                            ->addable(true)
                            ->deletable(true)
                            ->reorderable(true)
                            ->defaultItems(0)
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}