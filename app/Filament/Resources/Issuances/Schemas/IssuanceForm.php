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

class IssuanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
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
                        'pending'   => 'Pending',
                        'released'  => 'Released',
                        'issued'    => 'Issued',
                        'returned'  => 'Returned',
                    ])
                    ->default('pending')
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $now = now()->toDateString();
                        match ($state) {
                            'pending'   => $set('pending_at', $now),
                            'released'  => $set('released_at', $now),
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

                DatePicker::make('released_at')
                    ->label('Released Date')
                    ->visible(fn (callable $get) => $get('status') === 'released')
                    ->required(fn (callable $get) => $get('status') === 'released'),

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

                // Outer repeater — one row per unique item
                Repeater::make('issuance_items')
                    ->label('Items')
                    ->columns(1)
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(fn () => \App\Models\Item::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if (! $state) return;

                                // Check for duplicate item in outer repeater
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

                        // Inner repeater — sizes & quantities for the selected item
                        Repeater::make('sizes')
                            ->label('Sizes & Quantities')
                            ->columnSpanFull()
                            ->hidden(fn (callable $get) => ! $get('item_id'))
                            ->schema([
                                Select::make('size')
                                    ->label('Size')
                                    ->options(function (callable $get) {
                                        $itemId = $get('../../item_id');
                                        if (! $itemId) return [];
                                        return ItemVariant::where('item_id', $itemId)
                                            ->get()
                                            ->mapWithKeys(fn ($v) => [
                                                $v->size_label => "{$v->size_label} (stock: {$v->quantity})"
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

                                        $variant = ItemVariant::where('item_id', $itemId)
                                            ->where('size_label', $size)
                                            ->first();

                                        $stock = $variant?->quantity ?? 0;

                                        if ($quantity > 0 && $quantity > $stock) {
                                            $isHard = in_array($status, ['issued', 'released']);
                                            $color  = $isHard ? 'danger' : 'warning';
                                            $message = $isHard
                                                ? "🚫 Current stock is <strong>{$stock}</strong>. Save as pending or decrease quantity."
                                                : "⚠️ Current stock is <strong>{$stock}</strong>. You can save as pending but cannot issue/release until stock is sufficient.";

                                            return new HtmlString(
                                                "<div class='text-sm font-medium text-{$color}-700 
                                                    bg-{$color}-50 border border-{$color}-300 
                                                    rounded-lg px-3 py-2 mt-1'>
                                                    {$message}
                                                </div>"
                                            );
                                        }

                                        return new HtmlString(
                                            "<div class='text-sm text-gray-600 bg-gray-50 border 
                                                border-gray-200 rounded-lg px-3 py-2 mt-1'>
                                                📦 Current stock: <strong>{$stock}</strong>
                                            </div>"
                                        );
                                    }),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Size'),
                    ])
                    ->addActionLabel('Add Item')
                    ->required(),
            ]);
    }
}