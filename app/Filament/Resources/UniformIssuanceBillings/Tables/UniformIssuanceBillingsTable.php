<?php

namespace App\Filament\Resources\UniformIssuanceBillings\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class UniformIssuanceBillingsTable
{
    // ── Permission helper ─────────────────────────────────────────────────
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                TextColumn::make('employee_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employee_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'posted'   => '🟢 Posted',
                        'reliever' => '🟡 Reliever',
                        default    => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'posted'   => 'success',
                        'reliever' => 'warning',
                        default    => 'gray',
                    }),

                TextColumn::make('issuance_type')
                    ->label('Issuance Type')
                    ->badge()
                    ->color('indigo')
                    ->sortable(),

                TextColumn::make('uniformIssuance.site.name')
                    ->label('Site')
                    ->sortable()
                    ->placeholder('—'),

                // ── Items — minimal clickable summary ─────────────────────
                TextColumn::make('items_summary')
                    ->label('Items')
                    ->getStateUsing(function ($record): HtmlString {
                        $record->loadMissing('recipient.items.item');
                        $items = $record->recipient?->items ?? collect();

                        if ($items->isEmpty()) {
                            return new HtmlString('<span style="color:#9ca3af;font-size:12px;">—</span>');
                        }

                        $count    = $items->count();
                        $totalQty = $items->sum('quantity');

                        return new HtmlString(
                            "<span style='
                                display:inline-flex;align-items:center;gap:5px;
                                background:#eef2ff;border:1px solid #c7d2fe;
                                border-radius:999px;padding:2px 10px;
                                font-size:11px;font-weight:700;color:#4338ca;
                                cursor:pointer;
                            '>{$count} item(s) · {$totalQty} pc(s)</span>"
                        );
                    })
                    ->html()
                    ->placeholder('—')
                    ->action(
                        Action::make('view_items')
                            ->label('View Items')
                            ->modalHeading(fn ($record) => "Uniform Items — {$record->employee_name}")
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('lg')
                            ->form(function ($record): array {
                                $record->loadMissing('recipient.items.item');
                                $items = $record->recipient?->items ?? collect();

                                if ($items->isEmpty()) {
                                    return [
                                        \Filament\Forms\Components\Placeholder::make('no_items')
                                            ->label('')
                                            ->columnSpanFull()
                                            ->content(new HtmlString("
                                                <div style='text-align:center;padding:32px 0;color:#9ca3af;font-size:13px;'>
                                                    No items found.
                                                </div>
                                            ")),
                                    ];
                                }

                                $totalQty  = $items->sum('quantity');
                                $count     = $items->count();
                                $empName   = e($record->employee_name ?? '—');
                                $empStatus = $record->employee_status ?? 'posted';

                                $empStatusChip = match ($empStatus) {
                                    'posted'   => "<span style='background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:2px 10px;font-size:10px;font-weight:700;color:#059669;'>Posted</span>",
                                    'reliever' => "<span style='background:#fffbeb;border:1px solid #fde68a;border-radius:999px;padding:2px 10px;font-size:10px;font-weight:700;color:#d97706;'>Reliever</span>",
                                    default    => "<span style='background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:2px 10px;font-size:10px;color:#6b7280;'>" . ucfirst($empStatus) . "</span>",
                                };

                                $itemRowsHtml = '';
                                foreach ($items as $idx => $item) {
                                    $itemName = e($item->item?->name ?? "Item #{$item->item_id}");
                                    $size     = e($item->size ?? '—');
                                    $qty      = (int) $item->quantity;
                                    $rowBg    = $idx % 2 === 0 ? '#ffffff' : '#f9fafb';

                                    $itemRowsHtml .= "
                                        <tr style='background:{$rowBg};'>
                                            <td style='padding:9px 14px;font-size:12px;color:#111827;border-bottom:1px solid #f3f4f6;'>
                                                <div style='display:flex;align-items:center;gap:8px;'>
                                                    <div style='width:6px;height:6px;border-radius:50%;background:#6366f1;flex-shrink:0;'></div>
                                                    {$itemName}
                                                </div>
                                            </td>
                                            <td style='padding:9px 14px;font-size:12px;border-bottom:1px solid #f3f4f6;text-align:center;'>
                                                <span style='background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:2px 12px;font-size:11px;color:#374151;font-weight:500;'>{$size}</span>
                                            </td>
                                            <td style='padding:9px 14px;font-size:13px;font-weight:700;color:#1d4ed8;border-bottom:1px solid #f3f4f6;text-align:center;'>{$qty}</td>
                                        </tr>
                                    ";
                                }

                                return [
                                    \Filament\Forms\Components\Placeholder::make('items_header')
                                        ->label('')
                                        ->columnSpanFull()
                                        ->content(new HtmlString("
                                            <div style='
                                                background:linear-gradient(to right,#f0f4ff,#f8fafc);
                                                border:1px solid #e2e8f0;border-radius:12px;
                                                padding:14px 16px;
                                                display:flex;align-items:center;justify-content:space-between;
                                                margin-bottom:4px;
                                            '>
                                                <div>
                                                    <div style='font-size:14px;font-weight:700;color:#111827;'>{$empName}</div>
                                                    <div style='display:flex;align-items:center;gap:6px;margin-top:4px;'>{$empStatusChip}</div>
                                                </div>
                                                <div style='display:flex;gap:8px;'>
                                                    <div style='background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:8px 14px;text-align:center;'>
                                                        <div style='font-size:18px;font-weight:900;color:#4338ca;line-height:1;'>{$count}</div>
                                                        <div style='font-size:9px;color:#9ca3af;text-transform:uppercase;margin-top:2px;'>Items</div>
                                                    </div>
                                                    <div style='background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:8px 14px;text-align:center;'>
                                                        <div style='font-size:18px;font-weight:900;color:#1d4ed8;line-height:1;'>{$totalQty}</div>
                                                        <div style='font-size:9px;color:#9ca3af;text-transform:uppercase;margin-top:2px;'>Pieces</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ")),

                                    \Filament\Forms\Components\Placeholder::make('items_table')
                                        ->label('')
                                        ->columnSpanFull()
                                        ->content(new HtmlString("
                                            <div style='border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;'>
                                                <table style='width:100%;border-collapse:collapse;'>
                                                    <thead>
                                                        <tr style='background:#1e3a5f;'>
                                                            <th style='padding:9px 14px;text-align:left;font-size:11px;font-weight:700;color:#fff;text-transform:uppercase;'>Item</th>
                                                            <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#93c5fd;text-transform:uppercase;width:80px;'>Size</th>
                                                            <th style='padding:9px 14px;text-align:center;font-size:11px;font-weight:700;color:#93c5fd;text-transform:uppercase;width:70px;'>Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>{$itemRowsHtml}</tbody>
                                                </table>
                                                <div style='
                                                    display:flex;align-items:center;justify-content:flex-end;gap:16px;
                                                    background:#f8fafc;border-top:1px solid #e2e8f0;
                                                    padding:10px 16px;
                                                '>
                                                    <span style='font-size:11px;color:#9ca3af;margin-right:auto;'>Total</span>
                                                    <div style='text-align:center;'>
                                                        <div style='font-size:18px;font-weight:900;color:#1d4ed8;'>{$totalQty}</div>
                                                        <div style='font-size:9px;color:#9ca3af;text-transform:uppercase;'>Pieces</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ")),
                                ];
                            })
                    ),

                TextColumn::make('bill_status')
                    ->label('Bill Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'billed'       => 'Billed',
                        'not_billable' => 'Not Billable',
                        default        => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'billed'       => 'success',
                        'not_billable' => 'danger',
                        default        => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('endorsed_at')
                    ->label('Endorsed Date')
                    ->dateTime('M d, Y')
                    ->timezone('Asia/Manila')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('billed_at')
                    ->label('Bill Date')
                    ->dateTime('M d, Y')
                    ->timezone('Asia/Manila')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('M d, Y')
                    ->timezone('Asia/Manila')
                    ->sortable(),
            ])

            ->filters([])

            ->recordActions([

                // ── Mark as Billed ────────────────────────────────────────
                Action::make('mark_billed')
                    ->label('Mark as Billed')
                    ->icon('heroicon-s-check-circle')
                    ->color('success')
                    // Requires both the endorsement and the Spatie permission
                    ->visible(fn ($record) =>
                        ! is_null($record->endorsed_at)
                        && $record->bill_status !== 'billed'
                        && Auth::user()?->can('bill uniform-issuance-billing')
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Billed')
                    ->modalDescription('This will set the bill date to now and mark the record as Billed.')
                    ->modalSubmitActionLabel('Confirm')
                    ->action(function ($record): void {
                        $record->update([
                            'bill_status' => 'billed',
                            'billed_at'   => now(),
                            'billed_by'   => Auth::user()?->name ?? 'System',
                        ]);

                        Notification::make()
                            ->title("Marked as Billed — {$record->employee_name}")
                            ->success()
                            ->send();
                    }),

                // ── Mark as Not Billable ──────────────────────────────────
                Action::make('mark_not_billable')
                    ->label('Not Billable')
                    ->icon('heroicon-s-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        ! is_null($record->endorsed_at)
                        && $record->bill_status !== 'not_billable'
                        && Auth::user()?->can('bill uniform-issuance-billing')
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Not Billable')
                    ->modalDescription('This will mark the record as Not Billable and clear any billing data.')
                    ->modalSubmitActionLabel('Confirm')
                    ->action(function ($record): void {
                        $record->update([
                            'bill_status' => 'not_billable',
                            'billed_at'   => null,
                            'billed_by'   => null,
                        ]);

                        Notification::make()
                            ->title("Marked as Not Billable — {$record->employee_name}")
                            ->danger()
                            ->send();
                    }),

            ])

            ->toolbarActions([
                BulkActionGroup::make([

                    // ── Bulk Mark Billed ──────────────────────────────────
                    BulkAction::make('bulk_mark_billed')
                        ->label('Mark as Billed')
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->visible(fn () => Auth::user()?->can('bill uniform-issuance-billing'))
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected as Billed')
                        ->modalDescription('Only endorsed records not yet billed will be updated.')
                        ->modalSubmitActionLabel('Confirm')
                        ->action(function (Collection $records): void {
                            $eligible = $records->filter(
                                fn ($r) => ! is_null($r->endorsed_at) && $r->bill_status !== 'billed'
                            );

                            foreach ($eligible as $record) {
                                $record->update([
                                    'bill_status' => 'billed',
                                    'billed_at'   => now(),
                                    'billed_by'   => Auth::user()?->name ?? 'System',
                                ]);
                            }

                            $skipped = $records->count() - $eligible->count();

                            Notification::make()
                                ->title("{$eligible->count()} record(s) marked as Billed." . ($skipped > 0 ? " {$skipped} skipped." : ''))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── Bulk Mark Not Billable ────────────────────────────
                    BulkAction::make('bulk_mark_not_billable')
                        ->label('Mark as Not Billable')
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->visible(fn () => Auth::user()?->can('bill uniform-issuance-billing'))
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected as Not Billable')
                        ->modalDescription('Only billed records will be updated.')
                        ->modalSubmitActionLabel('Confirm')
                        ->action(function (Collection $records): void {
                            $eligible = $records->where('bill_status', 'billed');

                            foreach ($eligible as $record) {
                                $record->update([
                                    'bill_status' => 'not_billable',
                                    'billed_at'   => null,
                                    'billed_by'   => null,
                                ]);
                            }

                            $skipped = $records->count() - $eligible->count();

                            Notification::make()
                                ->title("{$eligible->count()} record(s) marked as Not Billable." . ($skipped > 0 ? " {$skipped} skipped." : ''))
                                ->danger()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ]);
    }
}