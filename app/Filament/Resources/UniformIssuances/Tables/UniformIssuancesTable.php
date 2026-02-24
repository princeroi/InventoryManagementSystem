<?php

namespace App\Filament\Resources\UniformIssuances\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\UniformIssuanceLog;
use App\Models\UniformIssuanceReturnItem;
use App\Models\ItemVariant;
use Filament\Notifications\Notification;


class UniformIssuancesTable
{
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    private static function variantMap(iterable $items): array
    {
        $pairs = collect($items)->map(fn ($i) => [
            'item_id' => $i->item_id,
            'size'    => $i->size,
        ])->unique()->values();

        if ($pairs->isEmpty()) return [];

        $variants = ItemVariant::query()
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($inner) use ($p) {
                        $inner->where('item_id', $p['item_id'])
                              ->where('size_label', $p['size']);
                    });
                }
            })
            ->get();

        $map = [];
        foreach ($variants as $v) {
            $map["{$v->item_id}:{$v->size_label}"] = $v;
        }

        return $map;
    }

    private static function isJsonSnapshot(?string $note): bool
    {
        if (! $note || ! str_starts_with(trim($note), '[')) return false;
        $decoded = json_decode($note, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Receiving Copy modal builder
    // ─────────────────────────────────────────────────────────────────────────

    private static function buildReceivingCopyModal($record): array
    {
        $record->loadMissing('site', 'issuanceType', 'logs', 'recipients');

        $siteName         = e($record->site?->name ?? '—');
        $issuanceTypeName = e($record->issuanceType?->name ?? '—');
        $issuanceDate     = $record->issued_at
            ? \Carbon\Carbon::parse($record->issued_at)->format('F d, Y')
            : now()->format('F d, Y');

        // Collect ALL logs that have snapshots (partial + issued), sorted oldest first
        $releaseLogs = $record->logs
            ->whereIn('action', ['partial', 'issued'])
            ->filter(fn ($log) => self::isJsonSnapshot($log->note))
            ->sortBy('created_at')
            ->values();

        $releaseCount   = $releaseLogs->count();
        $recipientCount = $record->recipients->count();
        $allUrl         = url("/receiving-copy/issuance/{$record->id}");

        // Count total slips
        if ($releaseCount > 0) {
            $totalSlips = 0;
            foreach ($releaseLogs as $log) {
                $snap      = json_decode($log->note, true);
                $employees = collect($snap)->pluck('label')->map(function ($label) {
                    $parts = explode(' — ', $label, 2);
                    return trim($parts[1] ?? '');
                })->filter()->unique()->count();
                $totalSlips += max($employees, 1);
            }
        } else {
            $totalSlips = $recipientCount;
        }
        $pageCount = (int) ceil($totalSlips / 2);

        $fields = [];

        // ── Status badge ──────────────────────────────────────────────────────
        $statusBadge = match ($record->status) {
            'partial'  => ['bg' => '#f59e0b', 'label' => 'PARTIAL — ' . $releaseCount . ' Release(s)'],
            'issued'   => ['bg' => '#059669', 'label' => 'ISSUED — ' . $releaseCount . ' Release(s)'],
            'returned' => ['bg' => '#dc2626', 'label' => 'RETURNED'],
            default    => ['bg' => '#6b7280', 'label' => strtoupper($record->status)],
        };

        // ── Top bar ───────────────────────────────────────────────────────────
        $fields[] = Placeholder::make('rc_topbar')
            ->label('')
            ->columnSpanFull()
            ->content(new HtmlString("
                <div style='
                    display:flex;align-items:center;justify-content:space-between;
                    padding:12px 16px;
                    background:linear-gradient(to right,#1e3a5f,#2563eb);
                    border-radius:10px;
                    margin-bottom:16px;
                '>
                    <div>
                        <div style='display:flex;align-items:center;gap:8px;'>
                            <div style='font-size:14px;font-weight:800;color:#fff;'>📄 Uniform Receiving Copy</div>
                            <span style='background:{$statusBadge['bg']};color:#fff;font-size:9px;font-weight:800;padding:2px 10px;border-radius:999px;letter-spacing:.06em;'>{$statusBadge['label']}</span>
                        </div>
                        <div style='font-size:11px;color:#93c5fd;margin-top:3px;'>
                            {$siteName} &nbsp;·&nbsp; {$issuanceTypeName} &nbsp;·&nbsp; {$issuanceDate}
                            &nbsp;·&nbsp; <strong style='color:#fff;'>{$totalSlips}</strong> slip(s) on <strong style='color:#fff;'>{$pageCount}</strong> A4 page(s)
                        </div>
                    </div>
                    <a
                        href='{$allUrl}'
                        target='_blank'
                        style='display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#fff;color:#1e3a5f;border-radius:8px;font-size:12px;font-weight:800;text-decoration:none;flex-shrink:0;'
                        onmouseover=\"this.style.opacity='.88'\"
                        onmouseout=\"this.style.opacity='1'\"
                    >
                        <svg width='14' height='14' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                            <polyline points='6 9 6 2 18 2 18 9'/>
                            <path d='M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2'/>
                            <rect x='6' y='14' width='12' height='8'/>
                        </svg>
                        Print All Slips
                    </a>
                </div>
            "));

        // ── If there are log snapshots — show each release as a card ──────────
        // This applies to BOTH partial and issued statuses
        if ($releaseCount > 0) {
            foreach ($releaseLogs as $batchIdx => $log) {
                $batchNo  = $batchIdx + 1;
                $logDate  = \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A');
                $logUrl   = url("/receiving-copy/log/{$log->id}");
                $snap     = json_decode($log->note, true);
                $isLast   = $batchIdx === $releaseCount - 1;
                $isFinal  = $isLast && $record->status === 'issued';

                // Card accent color: amber for partial releases, green for the final issued release
                $cardBorderColor = $isFinal ? '#059669' : '#f59e0b';
                $cardBgFrom      = $isFinal ? '#ecfdf5' : '#fffbeb';
                $cardBgTo        = $isFinal ? '#d1fae5' : '#fef3c7';
                $cardBorderHdr   = $isFinal ? '#a7f3d0' : '#fde68a';
                $badgeBg         = $isFinal ? '#059669' : '#f59e0b';
                $badgeLabel      = $isFinal ? "Final Release #{$batchNo}" : "Release #{$batchNo}";
                $textColor       = $isFinal ? '#065f46' : '#92400e';
                $subTextColor    = $isFinal ? '#047857' : '#78350f';
                $btnBg           = $isFinal ? '#059669' : '#f59e0b';
                $btnHover        = $isFinal ? '#047857' : '#d97706';
                $mb              = $isLast ? '0' : '12px';

                // Group by employee
                $byEmployee = [];
                foreach ($snap as $row) {
                    $label    = $row['label'] ?? '';
                    $released = (int) ($row['released'] ?? 0);
                    if ($released <= 0) continue;
                    $parts        = explode(' — ', $label, 2);
                    $itemPart     = trim($parts[0] ?? $label);
                    $employeeName = trim($parts[1] ?? 'Unknown');
                    $byEmployee[$employeeName][] = ['item' => $itemPart, 'qty' => $released];
                }

                $employeeCards = '';
                foreach ($byEmployee as $empName => $empItems) {
                    $empName  = e($empName);
                    $total    = array_sum(array_column($empItems, 'qty'));
                    $itemList = implode('', array_map(
                        fn ($i) => "<div style='font-size:10px;color:#374151;padding:2px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;'>"
                                 . "<span>" . e($i['item']) . "</span>"
                                 . "<span style='font-weight:700;color:#1d4ed8;'>" . $i['qty'] . "</span>"
                                 . "</div>",
                        $empItems
                    ));
                    $employeeCards .= "
                        <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-top:8px;'>
                            <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;'>
                                <span style='font-size:12px;font-weight:700;color:#111827;'>👤 {$empName}</span>
                                <span style='background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:1px 8px;font-size:10px;font-weight:700;color:#059669;'>{$total} item(s)</span>
                            </div>
                            {$itemList}
                        </div>
                    ";
                }

                $fields[] = Placeholder::make("rc_log_{$log->id}")
                    ->label('')
                    ->columnSpanFull()
                    ->content(new HtmlString("
                        <div style='border:2px solid {$cardBorderColor};border-radius:10px;overflow:hidden;margin-bottom:{$mb};'>
                            <div style='background:linear-gradient(to right,{$cardBgFrom},{$cardBgTo});border-bottom:1px solid {$cardBorderHdr};padding:10px 14px;display:flex;align-items:center;justify-content:space-between;'>
                                <div>
                                    <div style='display:flex;align-items:center;gap:8px;'>
                                        <span style='background:{$badgeBg};color:#fff;font-size:10px;font-weight:800;padding:2px 10px;border-radius:999px;'>{$badgeLabel}</span>
                                        <span style='font-size:11px;color:{$textColor};font-weight:600;'>{$logDate}</span>
                                    </div>
                                    <div style='font-size:10px;color:{$subTextColor};margin-top:3px;'>
                                        By: <strong>{$log->performed_by}</strong>
                                        &nbsp;·&nbsp; " . count($byEmployee) . " employee(s)
                                    </div>
                                </div>
                                <a
                                    href='{$logUrl}'
                                    target='_blank'
                                    style='display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:{$btnBg};color:#fff;border-radius:8px;font-size:11px;font-weight:800;text-decoration:none;flex-shrink:0;'
                                    onmouseover=\"this.style.background='{$btnHover}'\"
                                    onmouseout=\"this.style.background='{$btnBg}'\"
                                >
                                    <svg width='12' height='12' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                        <polyline points='6 9 6 2 18 2 18 9'/>
                                        <path d='M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2'/>
                                        <rect x='6' y='14' width='12' height='8'/>
                                    </svg>
                                    Print {$badgeLabel}
                                </a>
                            </div>
                            <div style='padding:10px 14px;'>
                                {$employeeCards}
                            </div>
                        </div>
                    "));
            }

            return $fields;
        }

        // ── Fallback: issued/returned with no log snapshots ───────────────────
        // Show per-recipient cards with individual print buttons
        $record->loadMissing('recipients.position', 'recipients.items.item');

        foreach ($record->recipients as $idx => $recipient) {
            $emp    = e($recipient->employee_name ?? '—');
            $pos    = e($recipient->position?->name ?? '—');
            $txn    = e($recipient->transaction_id ?? '—');
            $recUrl = url("/receiving-copy/recipient/{$recipient->id}");
            $mb     = ($idx < $record->recipients->count() - 1) ? '10px' : '0';

            $itemPreview = '';
            $total       = 0;
            foreach ($recipient->items as $i => $item) {
                $n    = e($item->item?->name ?? "Item #{$item->item_id}");
                $s    = e($item->size ?? '—');
                $q    = (int) ($item->released_quantity ?: $item->quantity);
                $total += $q;
                $bg   = $i % 2 === 0 ? '#ffffff' : '#f8fafc';
                $itemPreview .= "
                    <tr style='background:{$bg};'>
                        <td style='padding:4px 10px;font-size:11px;color:#111827;font-weight:500;border-bottom:1px solid #f1f5f9;'>{$n}</td>
                        <td style='padding:4px 10px;font-size:11px;color:#475569;text-align:center;border-bottom:1px solid #f1f5f9;'>
                            <span style='background:#f1f5f9;border-radius:999px;padding:1px 8px;font-size:10px;'>{$s}</span>
                        </td>
                        <td style='padding:4px 10px;font-size:12px;font-weight:800;color:#1d4ed8;text-align:center;border-bottom:1px solid #f1f5f9;'>{$q}</td>
                    </tr>
                ";
            }

            $fields[] = Placeholder::make("rc_rec_{$recipient->id}")
                ->label('')
                ->columnSpanFull()
                ->content(new HtmlString("
                    <div style='border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:{$mb};'>
                        <div style='background:linear-gradient(to right,#f0f4ff,#f8fafc);border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;'>
                            <div>
                                <div style='font-size:13px;font-weight:700;color:#111827;'>{$emp}</div>
                                <div style='font-size:10px;color:#6b7280;margin-top:1px;'>📌 {$pos} &nbsp;·&nbsp; 🔖 TXN: {$txn}</div>
                            </div>
                            <div style='display:flex;align-items:center;gap:8px;'>
                                <span style='background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:2px 10px;font-size:10px;font-weight:700;color:#059669;'>{$total} item(s)</span>
                                <a href='{$recUrl}' target='_blank'
                                    style='display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:#fff;border:1px solid #c7d2fe;border-radius:7px;font-size:10px;font-weight:700;color:#4f46e5;text-decoration:none;'
                                    onmouseover=\"this.style.background='#eef2ff'\"
                                    onmouseout=\"this.style.background='#fff'\"
                                >
                                    <svg width='11' height='11' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'>
                                        <polyline points='6 9 6 2 18 2 18 9'/>
                                        <path d='M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2'/>
                                        <rect x='6' y='14' width='12' height='8'/>
                                    </svg>
                                    Individual Copy
                                </a>
                            </div>
                        </div>
                        <table style='width:100%;border-collapse:collapse;'>
                            <thead>
                                <tr style='background:#1e3a5f;'>
                                    <th style='padding:6px 10px;text-align:left;font-size:9px;font-weight:700;color:#fff;text-transform:uppercase;'>Item</th>
                                    <th style='padding:6px 10px;text-align:center;font-size:9px;font-weight:700;color:#93c5fd;text-transform:uppercase;width:70px;'>Size</th>
                                    <th style='padding:6px 10px;text-align:center;font-size:9px;font-weight:700;color:#93c5fd;text-transform:uppercase;width:55px;'>Qty</th>
                                </tr>
                            </thead>
                            <tbody>{$itemPreview}</tbody>
                        </table>
                    </div>
                "));
        }

        return $fields;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE CONFIGURATION
    // ─────────────────────────────────────────────────────────────────────────

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('issuanceType.name')
                    ->label('Issuance Type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('recipients_count')
                    ->label('Employees')
                    ->counts('recipients'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'partial',
                        'success' => 'issued',
                        'danger'  => 'cancelled',
                        'gray'    => 'returned',
                    ]),

                TextColumn::make('status_date')
                    ->label('Date')
                    ->getStateUsing(fn ($record) => match ($record->status) {
                        'pending'   => $record->pending_at,
                        'partial'   => $record->partial_at,
                        'issued'    => $record->issued_at,
                        'returned'  => $record->returned_at,
                        'cancelled' => $record->cancelled_at,
                        default     => null,
                    })
                    ->formatStateUsing(fn ($state) => $state
                        ? \Carbon\Carbon::parse($state)->format('M d, Y')
                        : '—'
                    ),

                TextColumn::make('note')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->filters([])
            ->recordActions([

                ActionGroup::make([

                    // ── ISSUE ─────────────────────────────────────────────────
                    Action::make('issue')
                        ->label('Issue')
                        ->color('success')
                        ->icon('heroicon-s-check')
                        ->visible(fn ($record) =>
                            in_array($record->status, ['pending', 'partial']) &&
                            self::userCan('issue uniform-issuance')
                        )
                        ->modalHeading('Issue Issuance')
                        ->modalDescription('Enter the quantity to issue for each item.')
                        ->modalSubmitActionLabel('Confirm Issue')
                        ->form(function ($record): array {
                            $record->loadMissing('recipients.items.item');
                            $fields = [];

                            foreach ($record->recipients as $recipient) {
                                $txnLabel = $recipient->transaction_id
                                    ? " [{$recipient->transaction_id}]"
                                    : '';

                                foreach ($recipient->items as $issuanceItem) {
                                    $remaining = (int) $issuanceItem->remaining_quantity;
                                    if ($remaining <= 0) continue;

                                    $itemName = $issuanceItem->item?->name ?? "Item #{$issuanceItem->item_id}";
                                    $size     = $issuanceItem->size;
                                    $label    = $size ? "{$itemName} ({$size})" : $itemName;
                                    $label   .= " — {$recipient->employee_name}{$txnLabel}";
                                    $ordered  = $issuanceItem->quantity;

                                    $fields[] = TextInput::make("qty_{$issuanceItem->id}")
                                        ->label($label)
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue($remaining)
                                        ->default($remaining)
                                        ->suffix("/ {$remaining} remaining (ordered: {$ordered})")
                                        ->helperText("Max issuable: {$remaining}")
                                        ->required()
                                        ->dehydrated(true);
                                }
                            }

                            return $fields;
                        })
                        ->action(function ($record, array $data) {
                            $record->loadMissing('recipients.items.item');

                            $allItems   = $record->recipients->flatMap(fn ($r) => $r->items);
                            $variantMap = self::variantMap($allItems);

                            $allIssued    = true;
                            $itemSnapshot = [];
                            $insufficient = [];

                            foreach ($record->recipients as $recipient) {
                                foreach ($recipient->items as $issuanceItem) {
                                    $remaining = (int) $issuanceItem->remaining_quantity;
                                    if ($remaining <= 0) continue;

                                    $issueQty = (int) ($data["qty_{$issuanceItem->id}"] ?? 0);
                                    if ($issueQty <= 0) { $allIssued = false; continue; }

                                    $key   = "{$issuanceItem->item_id}:{$issuanceItem->size}";
                                    $stock = ($variantMap[$key] ?? null)?->quantity ?? 0;

                                    if ($issueQty > $stock) {
                                        $itemName       = $issuanceItem->item?->name ?? "Item #{$issuanceItem->item_id}";
                                        $insufficient[] = "{$itemName} ({$issuanceItem->size}): needs {$issueQty}, has {$stock}";
                                    }
                                }
                            }

                            if (! empty($insufficient)) {
                                Notification::make()
                                    ->title('Insufficient Stock')
                                    ->body('Cannot issue: ' . implode(' | ', $insufficient))
                                    ->danger()->persistent()->send();
                                return;
                            }

                            foreach ($record->recipients as $recipient) {
                                foreach ($recipient->items as $issuanceItem) {
                                    $remaining = (int) $issuanceItem->remaining_quantity;
                                    if ($remaining <= 0) continue;

                                    $issueQty     = (int) ($data["qty_{$issuanceItem->id}"] ?? 0);
                                    $toIssue      = min($issueQty, $remaining);
                                    $newRemaining = $remaining - $toIssue;

                                    $issuanceItem->update([
                                        'released_quantity'  => ($issuanceItem->released_quantity ?? 0) + $toIssue,
                                        'remaining_quantity' => $newRemaining,
                                    ]);

                                    if ($newRemaining > 0) $allIssued = false;

                                    $key     = "{$issuanceItem->item_id}:{$issuanceItem->size}";
                                    $variant = $variantMap[$key] ?? null;
                                    if ($variant && $toIssue > 0) {
                                        $variant->decrement('quantity', $toIssue);
                                        $variant->quantity -= $toIssue;
                                    }

                                    if ($toIssue > 0) {
                                        $itemName       = $issuanceItem->item?->name ?? "Item #{$issuanceItem->item_id}";
                                        $label          = $issuanceItem->size ? "{$itemName} ({$issuanceItem->size})" : $itemName;
                                        $itemSnapshot[] = [
                                            'label'     => "{$label} — {$recipient->employee_name}",
                                            'released'  => $toIssue,
                                            'remaining' => $newRemaining,
                                            'ordered'   => $issuanceItem->quantity,
                                        ];
                                    }
                                }
                            }

                            $newStatus           = $allIssued ? 'issued' : 'partial';
                            $record->logSnapshot = $itemSnapshot;
                            $record->forceLog    = true;
                            $record->update(['status' => $newStatus]);

                            if (! $record->wasChanged('status') && $record->forceLog) {
                                UniformIssuanceLog::create([
                                    'uniform_issuance_id' => $record->id,
                                    'action'              => 'partial',
                                    'performed_by'        => auth()->user()?->name ?? 'System',
                                    'note'                => ! empty($itemSnapshot) ? json_encode($itemSnapshot) : null,
                                ]);
                                $record->forceLog    = false;
                                $record->logSnapshot = null;
                            }

                            Notification::make()
                                ->title($allIssued ? 'Fully issued. Stock deducted.' : 'Partially issued. Stock deducted.')
                                ->success()->send();
                        }),

                    // ── RETURN ────────────────────────────────────────────────
                    Action::make('return')
                        ->label('Return')
                        ->color('warning')
                        ->icon('heroicon-s-arrow-path')
                        ->visible(fn ($record) =>
                            $record->status === 'issued' &&
                            self::userCan('return uniform-issuance')
                        )
                        ->modalHeading('Return Issuance')
                        ->modalDescription('Choose whether to restore stock.')
                        ->modalSubmitActionLabel('Confirm Return')
                        ->form(function ($record): array {
                            $record->loadMissing('recipients.items.item');
                            $fields = [];

                            $fields[] = Toggle::make('restore_stock')
                                ->label('Restore items back to stock?')
                                ->default(true)->live()
                                ->helperText('Turn on to add quantities back to inventory.')
                                ->columnSpanFull();

                            foreach ($record->recipients as $recipient) {
                                $txnLabel = $recipient->transaction_id ? " [{$recipient->transaction_id}]" : '';
                                foreach ($recipient->items as $issuanceItem) {
                                    $itemName = $issuanceItem->item?->name ?? "Item #{$issuanceItem->item_id}";
                                    $size     = $issuanceItem->size;
                                    $label    = ($size ? "{$itemName} ({$size})" : $itemName)
                                              . " — {$recipient->employee_name}{$txnLabel}";
                                    $fields[] = TextInput::make("qty_{$issuanceItem->id}")
                                        ->label($label)->numeric()->minValue(0)
                                        ->maxValue($issuanceItem->released_quantity ?: $issuanceItem->quantity)
                                        ->default($issuanceItem->released_quantity ?: $issuanceItem->quantity)
                                        ->suffix("/ " . ($issuanceItem->released_quantity ?: $issuanceItem->quantity))
                                        ->visible(fn (callable $get) => (bool) $get('restore_stock'))
                                        ->dehydrated(true);
                                }
                            }
                            return $fields;
                        })
                        ->action(function ($record, array $data) {
                            $record->loadMissing('recipients.items.item');
                            $restoreStock = (bool) ($data['restore_stock'] ?? true);
                            $allItems     = $record->recipients->flatMap(fn ($r) => $r->items);
                            $variantMap   = self::variantMap($allItems);
                            $itemSnapshot = [];
                            $performer    = auth()->user()?->name ?? 'System';

                            foreach ($record->recipients as $recipient) {
                                foreach ($recipient->items as $issuanceItem) {
                                    $returnQty = (int) ($data["qty_{$issuanceItem->id}"]
                                        ?? $issuanceItem->released_quantity ?? $issuanceItem->quantity);
                                    if ($returnQty <= 0) continue;

                                    if ($restoreStock) {
                                        $key     = "{$issuanceItem->item_id}:{$issuanceItem->size}";
                                        $variant = $variantMap[$key] ?? null;
                                        $variant?->increment('quantity', $returnQty);
                                    }

                                    UniformIssuanceReturnItem::create([
                                        'uniform_issuance_recipient_id' => $recipient->id,
                                        'item_id'  => $issuanceItem->item_id,
                                        'size'     => $issuanceItem->size,
                                        'quantity' => $returnQty,
                                        'returned_by' => $performer,
                                    ]);

                                    $itemName       = $issuanceItem->item?->name ?? "Item #{$issuanceItem->item_id}";
                                    $label          = $issuanceItem->size ? "{$itemName} ({$issuanceItem->size})" : $itemName;
                                    $itemSnapshot[] = ['label' => "{$label} — {$recipient->employee_name}", 'quantity' => $returnQty, 'action' => 'returned'];
                                }
                            }

                            $record->logSnapshot = $itemSnapshot;
                            $record->updateQuietly(['status' => 'returned', 'returned_at' => now()]);
                            UniformIssuanceLog::create([
                                'uniform_issuance_id' => $record->id,
                                'action'   => 'returned',
                                'performed_by' => $performer,
                                'note'     => ! empty($itemSnapshot) ? json_encode($itemSnapshot) : null,
                            ]);

                            Notification::make()
                                ->title($restoreStock ? 'Issuance returned. Stock restored.' : 'Issuance returned without restoring stock.')
                                ->color($restoreStock ? 'success' : 'warning')->send();
                        }),

                    // ── CANCEL ────────────────────────────────────────────────
                    Action::make('cancel')
                        ->label('Cancel')
                        ->color('danger')
                        ->icon('heroicon-s-x-mark')
                        ->visible(fn ($record) =>
                            $record->status === 'pending' &&
                            self::userCan('cancel uniform-issuance')
                        )
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                            Notification::make()->title('Issuance cancelled.')->danger()->send();
                        })
                        ->requiresConfirmation(),

                    EditAction::make()
                        ->visible(fn ($record) =>
                            $record->status === 'pending' &&
                            self::userCan('update uniform-issuance')
                        )
                        ->mutateRecordDataUsing(function (array $data, $record): array {
                            $record->loadMissing('recipients.items');
                            $data['employees'] = $record->recipients->map(fn ($recipient) => [
                                'employee_name'  => $recipient->employee_name,
                                'position_id'    => (string) $recipient->position_id,
                                'uniform_set_id' => $recipient->uniform_set_id
                                                        ? (string) $recipient->uniform_set_id
                                                        : 'manual',
                                'items' => $recipient->items->map(fn ($item) => [
                                    'item_id'  => (string) $item->item_id,
                                    'size'     => $item->size,
                                    'quantity' => (int) $item->quantity,
                                ])->toArray(),
                            ])->toArray();
                            return $data;
                        })
                        ->using(function ($record, array $data): \App\Models\UniformIssuance {
                            $employees = $data['employees'] ?? [];
                            unset($data['employees']);
                            $record->update($data);
                            $record->recipients()->each(fn ($r) => $r->items()->delete());
                            $record->recipients()->delete();
                            \App\Models\UniformIssuanceItem::$skipStockEvents = true;
                            foreach ($employees as $employee) {
                                $items = $employee['items'] ?? [];
                                unset($employee['items']);
                                $recipient = \App\Models\UniformIssuanceRecipient::create([
                                    'uniform_issuance_id' => $record->id,
                                    'employee_name'       => $employee['employee_name'],
                                    'position_id'         => $employee['position_id'],
                                    'uniform_set_id'      => ($employee['uniform_set_id'] !== 'manual') ? $employee['uniform_set_id'] : null,
                                    'mode'                => ($employee['uniform_set_id'] === 'manual') ? 'manual' : 'auto',
                                ]);
                                foreach ($items as $item) {
                                    \App\Models\UniformIssuanceItem::create([
                                        'uniform_issuance_recipient_id' => $recipient->id,
                                        'item_id'             => $item['item_id'],
                                        'size'                => $item['size'] ?? null,
                                        'quantity'            => $item['quantity'] ?? 1,
                                        'released_quantity'   => 0,
                                        'remaining_quantity'  => $item['quantity'] ?? 1,
                                    ]);
                                }
                            }
                            \App\Models\UniformIssuanceItem::$skipStockEvents = false;
                            return $record;
                        }),

                ]),

                // ── RECEIVING COPY (partial, issued, returned) ────────────────
                Action::make('view_receiving_copy')
                    ->label('Receiving Copy')
                    ->icon('heroicon-s-document-text')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['partial', 'issued', 'returned']))
                    ->modalHeading('Receiving Copy')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('3xl')
                    ->form(fn ($record): array => self::buildReceivingCopyModal($record)),

                // ── VIEW EMPLOYEES ────────────────────────────────────────────
                Action::make('view_employees')
                    ->label('View Employees')
                    ->icon('heroicon-s-users')
                    ->color('gray')
                    ->modalHeading('Recipients & Uniform Items')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('2xl')
                    ->form(function ($record): array {
                        $record->loadMissing('recipients.position', 'recipients.items.item', 'recipients.returnItems.item');
                        $recipients = $record->recipients;

                        if ($recipients->isEmpty()) {
                            return [Placeholder::make('no_recipients')->label('')->content(new HtmlString("<div style='text-align:center;padding:32px 0;color:#9ca3af;font-size:13px;'>No recipients found.</div>"))];
                        }

                        $fields     = [];
                        $isReturned = $record->status === 'returned';

                        foreach ($recipients as $i => $recipient) {
                            $employeeName = e($recipient->employee_name ?? 'Unknown Employee');
                            $positionName = e($recipient->position?->name ?? '—');
                            $txnId        = e($recipient->transaction_id ?? '—');
                            $isLast       = $i === $recipients->count() - 1;
                            $mb           = $isLast ? '0' : '20px';

                            $statusBadge = match ($record->status) {
                                'pending'   => ['label' => 'Pending',   'bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#d97706'],
                                'partial'   => ['label' => 'Partial',   'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'color' => '#7c3aed'],
                                'issued'    => ['label' => 'Issued',    'bg' => '#ecfdf5', 'border' => '#a7f3d0', 'color' => '#059669'],
                                'returned'  => ['label' => 'Returned',  'bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#dc2626'],
                                'cancelled' => ['label' => 'Cancelled', 'bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#dc2626'],
                                default     => ['label' => ucfirst($record->status), 'bg' => '#f9fafb', 'border' => '#e5e7eb', 'color' => '#6b7280'],
                            };

                            $statusHtml = "<span style='background:{$statusBadge['bg']};border:1px solid {$statusBadge['border']};border-radius:999px;padding:2px 10px;font-size:10px;font-weight:700;color:{$statusBadge['color']};text-transform:uppercase;letter-spacing:.05em;'>{$statusBadge['label']}</span>";
                            $thReturned = $isReturned ? "<th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase;'>Returned</th>" : '';

                            $returnMap = [];
                            foreach ($recipient->returnItems as $ri) {
                                $key = "{$ri->item_id}:{$ri->size}";
                                $returnMap[$key] = ($returnMap[$key] ?? 0) + $ri->quantity;
                            }

                            $itemRowsHtml = '';
                            foreach ($recipient->items as $idx => $item) {
                                $itemName        = e($item->item?->name ?? "Item #{$item->item_id}");
                                $size            = e($item->size ?? '—');
                                $qty             = (int) $item->quantity;
                                $released        = (int) $item->released_quantity;
                                $remaining       = (int) $item->remaining_quantity;
                                $returnedForItem = (int) ($returnMap["{$item->item_id}:{$item->size}"] ?? 0);
                                $rowBg           = $idx % 2 === 0 ? '#ffffff' : '#f9fafb';

                                $tdReturned = '';
                                if ($isReturned) {
                                    $tdReturned = $returnedForItem > 0
                                        ? "<td style='padding:9px 14px;border-bottom:1px solid #f3f4f6;text-align:center;'><span style='background:#fef2f2;border:1px solid #fecaca;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:700;color:#dc2626;'>{$returnedForItem}</span></td>"
                                        : "<td style='padding:9px 14px;font-size:12px;color:#d1d5db;border-bottom:1px solid #f3f4f6;text-align:center;'>—</td>";
                                }

                                $itemRowsHtml .= "<tr style='background:{$rowBg};'>
                                    <td style='padding:9px 14px;font-size:12px;color:#111827;border-bottom:1px solid #f3f4f6;'><div style='display:flex;align-items:center;gap:8px;'><div style='width:6px;height:6px;border-radius:50%;background:#6366f1;flex-shrink:0;'></div>{$itemName}</div></td>
                                    <td style='padding:9px 14px;font-size:12px;border-bottom:1px solid #f3f4f6;text-align:center;'><span style='background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:2px 12px;font-size:11px;color:#374151;font-weight:500;'>{$size}</span></td>
                                    <td style='padding:9px 14px;font-size:13px;font-weight:700;color:#1d4ed8;border-bottom:1px solid #f3f4f6;text-align:center;'>{$qty}</td>
                                    <td style='padding:9px 14px;font-size:13px;font-weight:700;color:#059669;border-bottom:1px solid #f3f4f6;text-align:center;'>{$released}</td>
                                    <td style='padding:9px 14px;font-size:13px;font-weight:700;color:#d97706;border-bottom:1px solid #f3f4f6;text-align:center;'>{$remaining}</td>
                                    {$tdReturned}
                                </tr>";
                            }

                            if (empty($itemRowsHtml)) {
                                $colspan = $isReturned ? '6' : '5';
                                $itemRowsHtml = "<tr><td colspan='{$colspan}' style='padding:16px;text-align:center;font-size:12px;color:#9ca3af;'>No items recorded.</td></tr>";
                            }

                            $totalOrdered  = $recipient->items->sum('quantity');
                            $totalReleased = $recipient->items->sum('released_quantity');
                            $totalReturned = $recipient->returnItems->sum('quantity');
                            $summaryReturnedHtml = $isReturned ? "<div style='width:1px;background:#e5e7eb;align-self:stretch;'></div><div style='text-align:center;'><div style='font-size:16px;font-weight:800;color:#dc2626;'>{$totalReturned}</div><div style='font-size:10px;color:#9ca3af;text-transform:uppercase;'>Returned</div></div>" : '';

                            $fields[] = Placeholder::make("recipient_{$recipient->id}")
                                ->label('')
                                ->content(new HtmlString("
                                    <div style='margin-bottom:{$mb};border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);'>
                                        <div style='background:linear-gradient(to right,#f0f4ff,#f8fafc);border-bottom:1px solid #e2e8f0;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;'>
                                            <div>
                                                <div style='font-size:14px;font-weight:700;color:#111827;'>{$employeeName}</div>
                                                <div style='font-size:11px;color:#6b7280;margin-top:2px;'>📌 {$positionName}</div>
                                            </div>
                                            <div style='display:flex;flex-direction:column;align-items:flex-end;gap:6px;'>
                                                {$statusHtml}
                                                <span style='background:#eef2ff;border:1px solid #c7d2fe;border-radius:999px;padding:3px 12px;font-size:11px;font-weight:700;color:#4f46e5;'>🔖 {$txnId}</span>
                                            </div>
                                        </div>
                                        <table style='width:100%;border-collapse:collapse;'>
                                            <thead><tr style='background:#f1f5f9;'>
                                                <th style='padding:9px 14px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;'>Item</th>
                                                <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;'>Size</th>
                                                <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;'>Ordered</th>
                                                <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;'>Issued</th>
                                                <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;'>Remaining</th>
                                                {$thReturned}
                                            </tr></thead>
                                            <tbody>{$itemRowsHtml}</tbody>
                                        </table>
                                        <div style='display:flex;align-items:center;gap:16px;background:#f8fafc;border-top:1px solid #e2e8f0;padding:10px 16px;justify-content:flex-end;'>
                                            <div style='font-size:11px;color:#9ca3af;margin-right:auto;'>Item Summary</div>
                                            <div style='text-align:center;'><div style='font-size:16px;font-weight:800;color:#1d4ed8;'>{$totalOrdered}</div><div style='font-size:10px;color:#9ca3af;text-transform:uppercase;'>Ordered</div></div>
                                            <div style='width:1px;background:#e5e7eb;align-self:stretch;'></div>
                                            <div style='text-align:center;'><div style='font-size:16px;font-weight:800;color:#059669;'>{$totalReleased}</div><div style='font-size:10px;color:#9ca3af;text-transform:uppercase;'>Issued</div></div>
                                            {$summaryReturnedHtml}
                                        </div>
                                    </div>
                                "));
                        }

                        return $fields;
                    }),

                // ── VIEW LOGS ─────────────────────────────────────────────────────────────────
                Action::make('view_logs')
                    ->label('View Logs')
                    ->icon('heroicon-s-clock')
                    ->color('gray')
                    ->modalHeading('Issuance Activity Log')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('lg')
                    ->form(function ($record): array {
                        $record->loadMissing('logs');
                        $logs = $record->logs;

                        if ($logs->isEmpty()) {
                            return [Placeholder::make('no_logs')->label('')->content(new HtmlString("<div style='text-align:center;padding:32px 0;color:#9ca3af;font-size:13px;'>No activity logs found.</div>"))];
                        }

                        $config = [
                            'created'   => ['color' => '#6366f1', 'bg' => '#eef2ff', 'border' => '#c7d2fe', 'label' => 'Created',   'icon' => 'M12 4v16m8-8H4'],
                            'pending'   => ['color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fde68a', 'label' => 'Pending',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'partial'   => ['color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'label' => 'Partial',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'issued'    => ['color' => '#059669', 'bg' => '#ecfdf5', 'border' => '#a7f3d0', 'label' => 'Issued',    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'returned'  => ['color' => '#ea580c', 'bg' => '#fff7ed', 'border' => '#fed7aa', 'label' => 'Returned',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                            'cancelled' => ['color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fecaca', 'label' => 'Cancelled', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];


                        // ── Summary bar at the top of the modal ──────────────────────────────
                        $fields = [];

                        // ── Individual log entries ────────────────────────────────────────────
                        foreach ($logs as $i => $log) {
                            $c      = $config[$log->action] ?? $config['created'];
                            $date   = \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y');
                            $time   = \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('h:i A');
                            $isLast = $i === $logs->count() - 1;
                            $line   = ! $isLast ? "<div style='width:2px;flex:1;background:#e5e7eb;margin-top:4px;min-height:20px;'></div>" : '';
                            $pb     = $isLast ? '0' : '16px';

                            $itemsHtml  = '';
                            $noteHtml   = '';
                            $rawNote    = $log->note;
                            $logSummary = '';

                            if ($rawNote) {
                                $decoded = json_decode($rawNote, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {

                                    // Per-log quantity summary chips
                                    $logQtyIssued   = 0;
                                    $logQtyReturned = 0;
                                    foreach ($decoded as $item) {
                                        $logQtyIssued   += (int) ($item['released'] ?? 0);
                                        $logQtyReturned += (int) ($item['quantity'] ?? 0);
                                    }

                                    $logSummaryParts = [];
                                    if ($logQtyIssued > 0) {
                                        $logSummaryParts[] = "<span style='background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:2px 10px;font-size:11px;font-weight:700;color:#059669;'>✅ {$logQtyIssued} item(s) issued</span>";
                                    }
                                    if ($logQtyReturned > 0) {
                                        $logSummaryParts[] = "<span style='background:#fff7ed;border:1px solid #fed7aa;border-radius:999px;padding:2px 10px;font-size:11px;font-weight:700;color:#ea580c;'>↩️ {$logQtyReturned} item(s) returned</span>";
                                    }
                                    if (! empty($logSummaryParts)) {
                                        $logSummary = "<div style='display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;'>" . implode('', $logSummaryParts) . "</div>";
                                    }

                                    // Row-by-row breakdown
                                    $rows = '';
                                    foreach ($decoded as $item) {
                                        $label = e($item['label'] ?? '');
                                        $chips = '';
                                        if (($item['ordered'] ?? null) !== null) {
                                            $chips .= "<span style='background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:1px 8px;font-size:11px;color:#6b7280;font-weight:500;'>📦 {$item['ordered']} ordered</span> ";
                                        }
                                        if (isset($item['released'])) {
                                            $chips .= "<span style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:1px 8px;font-size:11px;color:#2563eb;font-weight:600;'>📤 {$item['released']} issued</span> ";
                                            if (($item['remaining'] ?? 0) > 0) {
                                                $chips .= "<span style='background:#f5f3ff;border:1px solid #ddd6fe;border-radius:999px;padding:1px 8px;font-size:11px;color:#7c3aed;font-weight:600;'>⏳ {$item['remaining']} remaining</span>";
                                            }
                                        } elseif (isset($item['quantity'])) {
                                            $action = $item['action'] ?? '';
                                            $chips .= "<span style='background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:1px 8px;font-size:11px;color:#059669;font-weight:600;'>✅ {$item['quantity']} {$action}</span>";
                                        }
                                        $rows .= "<div style='display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid {$c['border']};'><span style='font-size:12px;font-weight:500;color:#374151;'>{$label}</span><div style='display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;'>{$chips}</div></div>";
                                    }
                                    $itemsHtml = "<div style='margin-top:8px;border-top:1px solid {$c['border']};padding-top:4px;'>{$rows}</div>";

                                } else {
                                    $noteHtml = "<div style='font-size:12px;color:#6b7280;margin-top:6px;padding-top:6px;border-top:1px solid {$c['border']};'>" . e($rawNote) . "</div>";
                                }
                            }

                            // Show "Cancelled" pill inline for cancelled logs with no items
                            if ($log->action === 'cancelled' && ! $rawNote) {
                                $logSummary = "<div style='display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;'>
                                    <span style='background:#fef2f2;border:1px solid #fecaca;border-radius:999px;padding:2px 10px;font-size:11px;font-weight:700;color:#dc2626;'>🚫 Issuance cancelled</span>
                                </div>";
                            }

                            $fields[] = Placeholder::make("log_{$log->id}")
                                ->label('')
                                ->content(new HtmlString("
                                    <div style='display:flex;gap:16px;'>
                                        <div style='display:flex;flex-direction:column;align-items:center;'>
                                            <div style='width:36px;height:36px;background:{$c['bg']};border:2px solid {$c['border']};border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;'>
                                                <svg style='width:16px;height:16px;' fill='none' stroke='{$c['color']}' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$c['icon']}'/></svg>
                                            </div>
                                            {$line}
                                        </div>
                                        <div style='flex:1;padding-bottom:{$pb};'>
                                            <div style='background:{$c['bg']};border:1px solid {$c['border']};border-radius:8px;padding:12px 14px;'>
                                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;'>
                                                    <span style='font-size:13px;font-weight:600;color:{$c['color']};'>{$c['label']}</span>
                                                    <span style='font-size:11px;color:#9ca3af;'>{$date} · {$time}</span>
                                                </div>
                                                <div style='font-size:12px;color:#6b7280;'>By: <strong style='color:#374151;'>{$log->performed_by}</strong></div>
                                                {$logSummary}
                                                {$itemsHtml}{$noteHtml}
                                            </div>
                                        </div>
                                    </div>
                                "));
                        }

                        return $fields;
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([

                    // ── BULK ISSUE ────────────────────────────────────────────
                    BulkAction::make('bulk_issue')
                        ->label('Issue Selected')
                        ->color('success')
                        ->icon('heroicon-s-check')
                        ->visible(fn () => self::userCan('issue uniform-issuance'))
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Issue')
                        ->modalDescription(fn ($records) =>
                            'Only pending issuances will be issued. ' .
                            $records->where('status', '!=', 'pending')->count() . ' record(s) will be skipped.'
                        )
                        ->action(function (Collection $records) {
                            $eligible   = $records->where('status', 'pending');
                            $allItems   = $eligible->flatMap(fn ($r) => $r->load('recipients.items')->recipients->flatMap(fn ($rec) => $rec->items));
                            $variantMap = self::variantMap($allItems);
                            $skipped    = [];
                            $issued     = 0;

                            foreach ($eligible as $record) {
                                $insufficient = [];
                                foreach ($record->recipients as $recipient) {
                                    foreach ($recipient->items as $item) {
                                        $key   = "{$item->item_id}:{$item->size}";
                                        $stock = ($variantMap[$key] ?? null)?->quantity ?? 0;
                                        if ($item->quantity > $stock) {
                                            $insufficient[] = "{$item->item?->name} ({$item->size}): needs {$item->quantity}, has {$stock}";
                                        }
                                    }
                                }
                                if (! empty($insufficient)) { $skipped[] = "Issuance #{$record->id}: " . implode(', ', $insufficient); continue; }

                                foreach ($record->recipients as $recipient) {
                                    foreach ($recipient->items as $item) {
                                        $key     = "{$item->item_id}:{$item->size}";
                                        $variant = $variantMap[$key] ?? null;
                                        if ($variant) { $variant->decrement('quantity', $item->quantity); $variant->quantity -= $item->quantity; }
                                        $item->update(['released_quantity' => $item->quantity, 'remaining_quantity' => 0]);
                                    }
                                }
                                $record->update(['status' => 'issued', 'issued_at' => now()]);
                                $issued++;
                            }

                            if ($issued > 0) Notification::make()->title("{$issued} issuance(s) issued. Stock deducted.")->success()->send();
                            if (! empty($skipped)) Notification::make()->title('Some skipped due to insufficient stock')->body(implode(' | ', $skipped))->danger()->persistent()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── BULK PRINT RECEIVING COPIES ───────────────────────────
                    BulkAction::make('bulk_print_receiving')
                        ->label('Print Receiving Copies')
                        ->color('success')
                        ->icon('heroicon-s-printer')
                        ->visible(fn () => self::userCan('issue uniform-issuance'))
                        ->action(function (Collection $records) {
                            $eligible = $records->whereIn('status', ['partial', 'issued', 'returned']);

                            if ($eligible->isEmpty()) {
                                Notification::make()
                                    ->title('No eligible issuances selected.')
                                    ->body('Only partial, issued, or returned issuances have receiving copies.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $ids     = $eligible->pluck('id')->implode(',');
                            $url     = url("/receiving-copy/bulk?ids={$ids}");
                            $skipped = $records->count() - $eligible->count();
                            $msg     = $skipped > 0 ? " ({$skipped} record(s) skipped)" : '';

                            Notification::make()
                                ->title("Opening {$eligible->count()} issuance(s){$msg}")
                                ->body('A new tab will open with all slips — 2 per A4 page. Partial issuances will show per-release slips.')
                                ->success()
                                ->actions([
                                    Action::make('open')
                                        ->label('🖨️ Open Print Page')
                                        ->url($url, shouldOpenInNewTab: true)
                                        ->button(),
                                ])
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── BULK RETURN ───────────────────────────────────────────
                    BulkAction::make('bulk_return')
                        ->label('Return Selected')
                        ->color('warning')
                        ->icon('heroicon-s-arrow-path')
                        ->visible(fn () => self::userCan('return uniform-issuance'))
                        ->modalHeading('Bulk Return')
                        ->modalSubmitActionLabel('Confirm Return')
                        ->form(fn (): array => [
                            Toggle::make('restore_stock')
                                ->label('Restore items back to stock?')
                                ->default(true)->live()
                                ->helperText('Turn on to restore full issued quantities to inventory for all selected records.')
                                ->columnSpanFull(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $eligible     = $records->where('status', 'issued');
                            $restoreStock = (bool) ($data['restore_stock'] ?? true);
                            $returned     = 0;
                            $performer    = auth()->user()?->name ?? 'System';

                            if ($restoreStock) {
                                $allItems   = $eligible->flatMap(fn ($r) => $r->load('recipients.items')->recipients->flatMap(fn ($rec) => $rec->items));
                                $variantMap = self::variantMap($allItems);
                                foreach ($eligible as $record) {
                                    foreach ($record->recipients as $recipient) {
                                        foreach ($recipient->items as $item) {
                                            $restoreQty = $item->released_quantity ?: $item->quantity;
                                            $key        = "{$item->item_id}:{$item->size}";
                                            ($variantMap[$key] ?? null)?->increment('quantity', $restoreQty);
                                            UniformIssuanceReturnItem::create(['uniform_issuance_recipient_id' => $recipient->id, 'item_id' => $item->item_id, 'size' => $item->size, 'quantity' => $restoreQty, 'returned_by' => $performer]);
                                        }
                                    }
                                    $record->updateQuietly(['status' => 'returned', 'returned_at' => now()]);
                                    UniformIssuanceLog::create(['uniform_issuance_id' => $record->id, 'action' => 'returned', 'performed_by' => $performer, 'note' => null]);
                                    $returned++;
                                }
                            } else {
                                foreach ($eligible as $record) {
                                    $record->updateQuietly(['status' => 'returned', 'returned_at' => now()]);
                                    UniformIssuanceLog::create(['uniform_issuance_id' => $record->id, 'action' => 'returned', 'performed_by' => $performer, 'note' => null]);
                                    $returned++;
                                }
                            }

                            Notification::make()
                                ->title("{$returned} issuance(s) returned. " . ($restoreStock ? 'Stock restored.' : 'Stock not restored.'))
                                ->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── BULK CANCEL ───────────────────────────────────────────
                    BulkAction::make('bulk_cancel')
                        ->label('Cancel Selected')
                        ->color('danger')
                        ->icon('heroicon-s-x-mark')
                        ->visible(fn () => self::userCan('cancel uniform-issuance'))
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Cancel')
                        ->modalDescription(fn ($records) =>
                            'Only pending issuances will be cancelled. ' .
                            $records->where('status', '!=', 'pending')->count() . ' record(s) will be skipped.'
                        )
                        ->action(function (Collection $records) {
                            $eligible = $records->where('status', 'pending');
                            foreach ($eligible as $record) {
                                $record->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                            }
                            $skipped = $records->count() - $eligible->count();
                            Notification::make()
                                ->title("{$eligible->count()} issuance(s) cancelled." . ($skipped > 0 ? " {$skipped} skipped." : ''))
                                ->danger()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ]);
    }
}