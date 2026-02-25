<?php

namespace App\Filament\Resources\UniformIssuances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
use Filament\Actions\Action;

class UniformIssuanceForm
{
    // ── Employee status options (centralised) ─────────────────────────────────
    public const EMPLOYEE_STATUS_OPTIONS = [
        'posted'   => 'Posted',
        'reliever' => 'Reliever',
    ];

    private static function stockForVariant(?int $itemId, ?string $size): int
    {
        if (! $itemId || ! $size) return 0;

        return ItemVariant::where('item_id', $itemId)
            ->where('size_label', $size)
            ->value('quantity') ?? 0;
    }

    private static function buildSubtotalHtml(array $employees): HtmlString
    {
        $aggregated = [];

        foreach ($employees as $employee) {
            foreach ($employee['items'] ?? [] as $item) {
                $itemId = $item['item_id'] ?? null;
                $size   = $item['size']    ?? null;
                $qty    = (int) ($item['quantity'] ?? 1);

                if (! $itemId || ! $size) continue;

                $key = "{$itemId}:{$size}";

                if (! isset($aggregated[$key])) {
                    $itemName = Item::find($itemId)?->name ?? "Item #{$itemId}";
                    $stock    = self::stockForVariant((int) $itemId, $size);

                    $aggregated[$key] = [
                        'label'    => "{$itemName} ({$size})",
                        'totalQty' => 0,
                        'stock'    => $stock,
                    ];
                }

                $aggregated[$key]['totalQty'] += $qty;
            }
        }

        if (empty($aggregated)) {
            return new HtmlString("
                <div style='font-size:12px;color:#9ca3af;background:#f9fafb;border:1px dashed #e5e7eb;border-radius:8px;padding:12px 16px;text-align:center;'>
                    No items added yet. Add employees and items above to see the subtotal.
                </div>
            ");
        }

        $rows = ''; $hasWarn = false; $hasDanger = false;

        foreach ($aggregated as $row) {
            $label = e($row['label']);
            $total = $row['totalQty'];
            $stock = $row['stock'];
            $diff  = $stock - $total;

            if ($diff < 0) {
                $hasDanger  = true;
                $statusIcon = '🚫'; $qtyColor = '#dc2626'; $qtyBg = '#fef2f2'; $qtyBorder = '#fecaca';
                $stockColor = '#dc2626'; $rowBorder = '#fecaca'; $rowBg = '#fff5f5';
                $diffHtml   = "<span style='color:#dc2626;font-weight:700;font-size:11px;'>".abs($diff)." short</span>";
            } elseif ($diff === 0) {
                $hasWarn    = true;
                $statusIcon = '⚠️'; $qtyColor = '#d97706'; $qtyBg = '#fffbeb'; $qtyBorder = '#fde68a';
                $stockColor = '#d97706'; $rowBorder = '#fde68a'; $rowBg = '#fffdf0';
                $diffHtml   = "<span style='color:#d97706;font-weight:700;font-size:11px;'>exact</span>";
            } else {
                $statusIcon = '✅'; $qtyColor = '#059669'; $qtyBg = '#ecfdf5'; $qtyBorder = '#a7f3d0';
                $stockColor = '#374151'; $rowBorder = '#e5e7eb'; $rowBg = '#ffffff';
                $diffHtml   = "<span style='color:#6b7280;font-size:11px;'>+{$diff} left</span>";
            }

            $rows .= "
                <tr style='background:{$rowBg};border-bottom:1px solid {$rowBorder};'>
                    <td style='padding:6px 12px;font-size:12px;color:#111827;font-weight:500;'>{$statusIcon} {$label}</td>
                    <td style='padding:6px 12px;text-align:center;'>
                        <span style='background:{$qtyBg};border:1px solid {$qtyBorder};border-radius:999px;padding:2px 10px;font-size:12px;font-weight:700;color:{$qtyColor};'>{$total}</span>
                    </td>
                    <td style='padding:6px 12px;text-align:center;font-size:12px;color:{$stockColor};font-weight:600;'>{$stock}</td>
                    <td style='padding:6px 12px;text-align:center;'>{$diffHtml}</td>
                </tr>
            ";
        }

        if ($hasDanger) {
            $bannerBg = '#fef2f2'; $bannerBorder = '#fecaca'; $bannerColor = '#dc2626';
            $bannerIcon = '🚫'; $bannerText = 'Insufficient stock for some items. Reduce quantities or set status to Pending.';
        } elseif ($hasWarn) {
            $bannerBg = '#fffbeb'; $bannerBorder = '#fde68a'; $bannerColor = '#d97706';
            $bannerIcon = '⚠️'; $bannerText = 'Stock is exactly met. No buffer remaining after this issuance.';
        } else {
            $bannerBg = '#ecfdf5'; $bannerBorder = '#a7f3d0'; $bannerColor = '#059669';
            $bannerIcon = '✅'; $bannerText = 'All items have sufficient stock.';
        }

        return new HtmlString("
            <div style='border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;'>
                <div style='background:linear-gradient(to right,#f8fafc,#f1f5f9);border-bottom:1px solid #e2e8f0;padding:8px 14px;display:flex;align-items:center;justify-content:space-between;'>
                    <span style='font-size:12px;font-weight:700;color:#374151;letter-spacing:0.05em;text-transform:uppercase;'>📦 Item Subtotal (All Employees)</span>
                    <span style='font-size:11px;color:#9ca3af;'>" . count($aggregated) . " unique item(s)</span>
                </div>
                <table style='width:100%;border-collapse:collapse;background:#fff;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:6px 12px;text-align:left;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;'>Item</th>
                            <th style='padding:6px 12px;text-align:center;font-size:10px;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.05em;'>Total Needed</th>
                            <th style='padding:6px 12px;text-align:center;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.05em;'>In Stock</th>
                            <th style='padding:6px 12px;text-align:center;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.05em;'>Balance</th>
                        </tr>
                    </thead>
                    <tbody>{$rows}</tbody>
                </table>
                <div style='background:{$bannerBg};border-top:1px solid {$bannerBorder};padding:7px 14px;font-size:12px;color:{$bannerColor};font-weight:500;'>
                    {$bannerIcon} {$bannerText}
                </div>
            </div>
        ");
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
                    ->required()
                    ->helperText('e.g. New Hire · Salary Deduct · Annual · Additional'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'issued'  => 'Issued',
                    ])
                    ->default('pending')
                    ->live()
                    ->afterStateUpdated(function ($set, $state): void {
                        $now = now()->toDateString();
                        match ($state) {
                            'pending'   => $set('pending_at', $now),
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

                // ── Transmittal Section ───────────────────────────────────────
                Section::make('📮 Transmittal')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => ! ($record?->is_for_transmit))
                    ->schema([
                        Placeholder::make('existing_transmittal_number')
                            ->label('Transmittal Number')
                            ->visible(fn ($record) => (bool) $record?->transmittal_id)
                            ->content(fn ($record): HtmlString => new HtmlString("
                                <span style='display:inline-flex;align-items:center;gap:6px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:800;color:#1d4ed8;letter-spacing:.04em;'>
                                    📋 " . e($record?->transmittal?->transmittal_number ?? '—') . "
                                </span>
                                <span style='margin-left:8px;font-size:12px;color:#6b7280;'>
                                    → " . e($record?->transmitted_to ?? '') . "
                                </span>
                            "))
                            ->columnSpanFull(),

                        Toggle::make('is_for_transmit')
                            ->label('Mark for Transmittal')
                            ->helperText('A transmittal record (HR-YYYYMMDD-XXXX) will be auto-created and linked to this issuance.')
                            ->live()
                            ->default(false)
                            ->disabled(fn ($record) => (bool) $record?->transmittal_id)
                            ->columnSpanFull(),

                        TextInput::make('transmitted_to')
                            ->label('Transmitted To')
                            ->placeholder('e.g. Main Office, Warehouse, Site B...')
                            ->required(fn ($get) => (bool) $get('is_for_transmit'))
                            ->visible(fn ($get, $record) => (bool) $get('is_for_transmit') && ! $record?->transmittal_id)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('transmittal_purpose')
                            ->label('Purpose')
                            ->placeholder('FOR ISSUANCE & SIGNATURE')
                            ->default('FOR ISSUANCE & SIGNATURE')
                            ->required(fn ($get) => (bool) $get('is_for_transmit'))
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('is_for_transmit'))
                            ->helperText('Printed on the transmittal form under "Purpose".'),

                        TextInput::make('transmittal_instructions')
                            ->label('Instructions')
                            ->placeholder('PLEASE RETURN RECEIVING COPY')
                            ->default('PLEASE RETURN RECEIVING COPY')
                            ->required(fn ($get) => (bool) $get('is_for_transmit'))
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('is_for_transmit'))
                            ->helperText('Printed on the transmittal form under "Instructions".'),
                    ])
                    ->columns(1),

                // ── Employees repeater ────────────────────────────────────────
                Repeater::make('employees')
                    ->columnSpanFull()
                    ->collapsible()
                    ->cloneable(false)
                    ->addActionLabel('Add Another Employee')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->live(debounce: 300)
                    // ── Show name + status badge in collapsed label ────────────
                    ->itemLabel(function (array $state): ?string {
                        $name   = $state['employee_name'] ?? 'New Employee';
                        $status = $state['employee_status'] ?? null;
                        $badge  = match ($status) {
                            'posted'   => ' 🟢 Posted',
                            'reliever' => ' 🟡 Reliever',
                            default    => '',
                        };
                        return $name . $badge;
                    })
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
                    ->schema([
                        TextInput::make('employee_name')
                            ->label('Employee Name')
                            ->required()
                            ->live(debounce: '500ms')
                            ->columnSpan(1),

                        // ── NEW: Employee Status ──────────────────────────────
                        Select::make('employee_status')
                            ->label('Employee Status')
                            ->options(self::EMPLOYEE_STATUS_OPTIONS)
                            ->default('posted')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                // Reset set & items when status changes —
                                // the available sets will be different
                                $set('uniform_set_id', null);
                                $set('items', []);
                            })
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
                                $positionId     = $get('position_id');
                                $employeeStatus = $get('employee_status');
                                $options        = ['manual' => '✏️ Manual Input'];

                                if ($positionId) {
                                    $query = UniformSet::where('department_id', Filament::getTenant()?->id)
                                        ->where('position_id', $positionId);

                                    // Show sets with:
                                    //   a) null employee_status  → applies to all
                                    //   b) matching employee_status → specific to this type
                                    if ($employeeStatus) {
                                        $query->where(function ($q) use ($employeeStatus) {
                                            $q->whereNull('employee_status')
                                              ->orWhere('employee_status', $employeeStatus);
                                        });
                                    }

                                    $sets = $query->get()->mapWithKeys(function ($set) {
                                        $statusTag = match ($set->employee_status) {
                                            'posted'   => ' 🟢',
                                            'reliever' => ' 🟡',
                                            default    => '',
                                        };
                                        return [$set->id => $set->name . $statusTag];
                                    })->toArray();

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

                        // ── Items repeater ────────────────────────────────────
                        Repeater::make('items')
                            ->live(debounce: 300)
                            ->collapsible(false)
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->addActionLabel('+ Add Item')
                            ->addable(true)
                            ->deletable(false)
                            ->columnSpanFull()
                            ->itemLabel(null)
                            ->extraAttributes([
                                'class' => 'compact-items-repeater',
                                'style' => 'background:transparent;',
                            ])
                            ->schema([
                                Select::make('item_id')
                                    ->hiddenLabel()
                                    ->placeholder('Select Item')
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set): void {
                                        $set('size', null);
                                    })
                                    ->columnSpan(4),

                                Select::make('size')
                                    ->hiddenLabel()
                                    ->placeholder('Size')
                                    ->options(function ($get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) return [];

                                        return ItemVariant::where('item_id', $itemId)
                                            ->get()
                                            ->mapWithKeys(fn ($v) => [
                                                $v->size_label => "{$v->size_label} (stock: {$v->quantity})"
                                            ])
                                            ->all();
                                    })
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function ($state, $get): void {
                                        if (! $state) return;

                                        $itemId   = $get('item_id');
                                        $allItems = $get('../../items') ?? [];

                                        $sameCombo = collect($allItems)->filter(
                                            fn ($i) => ($i['item_id'] ?? null) == $itemId
                                                && ($i['size']    ?? null) == $state
                                        );

                                        if ($sameCombo->count() > 1) {
                                            Notification::make()
                                                ->title('Size already exists for this item')
                                                ->body('Increase the quantity instead of adding a duplicate size.')
                                                ->danger()
                                                ->persistent()
                                                ->send();
                                        }
                                    })
                                    ->columnSpan(3),

                                TextInput::make('quantity')
                                    ->hiddenLabel()
                                    ->placeholder('Qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 400)
                                    ->columnSpan(2),

                                Placeholder::make('stock_warning')
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->content(function ($get): ?HtmlString {
                                        $itemId   = $get('item_id');
                                        $size     = $get('size');
                                        $quantity = (int) $get('quantity');
                                        $status   = $get('../../../../../../status');

                                        if (! $itemId || ! $size) return null;

                                        $stock = self::stockForVariant((int) $itemId, $size);

                                        if ($quantity <= $stock) {
                                            return new HtmlString("
                                                <div style='font-size:11px;color:#059669;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:4px;padding:3px 8px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;'>
                                                    ✅ Stock: <strong>{$stock}</strong>
                                                </div>
                                            ");
                                        }

                                        $isHard = in_array($status, ['partial', 'issued']);

                                        if ($isHard) {
                                            return new HtmlString("
                                                <div style='font-size:11px;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:3px 8px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;'>
                                                    🚫 Only <strong>{$stock}</strong> in stock — <strong>{$quantity}</strong> requested.
                                                </div>
                                            ");
                                        }

                                        return new HtmlString("
                                            <div style='font-size:11px;color:#d97706;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:3px 8px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;'>
                                                ⚠️ Stock: <strong>{$stock}</strong> — requested: <strong>{$quantity}</strong> Pending only.
                                            </div>
                                        ");
                                    }),

                                Placeholder::make('_delete')
                                    ->hiddenLabel()
                                    ->columnSpan(1)
                                    ->content(new HtmlString("
                                        <button
                                            type='button'
                                            x-data
                                            x-on:click=\"
                                                let repeater = \$el.closest('[wire\\:key]');
                                                let key = repeater ? repeater.getAttribute('wire:key') : null;
                                                if (key) {
                                                    let parts = key.split('.');
                                                    let itemKey = parts.pop();
                                                    let repeaterKey = parts.join('.');
                                                    \$wire.dispatchFormEvent('repeater::deleteItem', repeaterKey, itemKey);
                                                }
                                            \"
                                            style='display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid #fca5a5;background:#ffffff;border-radius:8px;color:#ef4444;cursor:pointer;transition:background 0.15s,border-color 0.15s;'
                                            onmouseover=\"this.style.background='#fef2f2';this.style.borderColor='#f87171';\"
                                            onmouseout=\"this.style.background='#ffffff';this.style.borderColor='#fca5a5';\"
                                            title='Remove item'
                                        >
                                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='1.8'>
                                                <path stroke-linecap='round' stroke-linejoin='round' d='M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6'/>
                                                <path stroke-linecap='round' stroke-linejoin='round' d='M10 11v6M14 11v6'/>
                                            </svg>
                                        </button>
                                    ")),
                            ])
                            ->columns(12),
                    ])
                    ->columns(4), // 4-col grid: name | status | position | set

                // ── Live subtotal panel ───────────────────────────────────────
                Placeholder::make('items_subtotal')
                    ->label('')
                    ->columnSpanFull()
                    ->live(debounce: 400)
                    ->content(function ($get): HtmlString {
                        $employees = $get('employees') ?? [];
                        return self::buildSubtotalHtml($employees);
                    }),
            ]);
    }
}