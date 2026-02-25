<?php

namespace App\Filament\Resources\UniformIssuances\Pages;

use App\Filament\Resources\UniformIssuances\UniformIssuanceResource;
use App\Models\UniformIssuanceRecipient;
use App\Models\UniformIssuanceItem;
use App\Models\UniformIssuanceLog;
use App\Models\Transmittal;
use App\Models\ItemVariant;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class ListUniformIssuances extends ListRecords
{
    protected static string $resource = UniformIssuanceResource::class;

    private function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    protected function getStatusCounts(): array
    {
        $tenantId = Filament::getTenant()?->id;

        $rows = \App\Models\UniformIssuance::query()
            ->where('department_id', $tenantId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return array_merge(['all' => array_sum($rows)], $rows);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => $this->userCan('create uniform-issuance'))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['department_id'] = Filament::getTenant()->id;
                    return $data;
                })
                ->using(function (array $data, string $model): \App\Models\UniformIssuance {
                    $employees     = $data['employees'] ?? [];
                    $isForTransmit = (bool) ($data['is_for_transmit'] ?? false);
                    $transmittedTo = trim($data['transmitted_to'] ?? '');

                    unset($data['employees']);

                    // ── Stock validation ──────────────────────────────────────
                    if (in_array($data['status'] ?? 'pending', ['partial', 'issued'])) {
                        $insufficient = [];

                        foreach ($employees as $employee) {
                            foreach ($employee['items'] ?? [] as $item) {
                                $itemId   = $item['item_id'] ?? null;
                                $size     = $item['size'] ?? null;
                                $quantity = (int) ($item['quantity'] ?? 1);

                                if (! $itemId || ! $size) continue;

                                $variant = \App\Models\ItemVariant::where('item_id', $itemId)
                                    ->where('size_label', $size)
                                    ->first();
                                $stock = $variant?->quantity ?? 0;

                                if ($quantity > $stock) {
                                    $itemName       = \App\Models\Item::find($itemId)?->name ?? "Item #{$itemId}";
                                    $employeeName   = $employee['employee_name'] ?? 'Unknown';
                                    $insufficient[] = "{$itemName} ({$size}) for {$employeeName}: needs {$quantity}, has {$stock}";
                                }
                            }
                        }

                        if (! empty($insufficient)) {
                            Notification::make()
                                ->title('Insufficient Stock — Issuance not saved.')
                                ->body(implode("\n", array_slice($insufficient, 0, 5)) . (count($insufficient) > 5 ? "\n...and more." : ''))
                                ->danger()->persistent()->send();

                            throw new \Illuminate\Validation\ValidationException(validator([], []));
                        }
                    }

                    // ── Create the issuance ───────────────────────────────────
                    $issuance = $model::create($data);

                    \App\Models\UniformIssuanceItem::$skipStockEvents = true;

                    foreach ($employees as $employee) {
                        $items = $employee['items'] ?? [];
                        unset($employee['items']);

                        $recipient = UniformIssuanceRecipient::create([
                            'uniform_issuance_id' => $issuance->id,
                            'employee_name'       => $employee['employee_name'],
                            'position_id'         => $employee['position_id'],
                            'uniform_set_id'      => ($employee['uniform_set_id'] !== 'manual') ? $employee['uniform_set_id'] : null,
                            'mode'                => ($employee['uniform_set_id'] === 'manual') ? 'manual' : 'auto',
                        ]);

                        foreach ($items as $item) {
                            UniformIssuanceItem::create([
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

                    // ── Deduct stock if status requires it ────────────────────
                    if (in_array($issuance->status, ['partial', 'issued'])) {
                        $issuance->loadMissing('recipients.items');

                        foreach ($issuance->recipients as $recipient) {
                            foreach ($recipient->items as $issuanceItem) {
                                if (! $issuanceItem->size) continue;

                                ItemVariant::where('item_id', $issuanceItem->item_id)
                                    ->where('size_label', $issuanceItem->size)
                                    ->first()
                                    ?->decrement('quantity', $issuanceItem->quantity);

                                $issuanceItem->update([
                                    'released_quantity'  => $issuanceItem->quantity,
                                    'remaining_quantity' => 0,
                                ]);
                            }
                        }

                        UniformIssuanceLog::create([
                            'uniform_issuance_id' => $issuance->id,
                            'action'              => $issuance->status,
                            'performed_by'        => auth()->user()?->name ?? 'System',
                            'note'                => 'Stock deducted on creation.',
                        ]);
                    }

                    // ── Auto-create Transmittal only if flagged AND status is issued ──
                    if ($isForTransmit && $transmittedTo && $issuance->status === 'issued') {
                        $tenant = Filament::getTenant();
                        $user   = auth()->user();

                        $transmittal = Transmittal::create([
                            'transmittal_number'     => Transmittal::generateNumber($tenant->id),
                            'department_id'          => $tenant->id,
                            'transmitted_by'         => $user?->name ?? 'System',
                            'transmitted_by_user_id' => $user?->id,
                            'transmitted_to'         => $transmittedTo,
                            'items_summary'          => Transmittal::buildSummaryFromIssuance($issuance),
                        ]);

                        // Link transmittal back to the issuance (quiet — no log event)
                        $issuance->updateQuietly(['transmittal_id' => $transmittal->id]);
                    }

                    return $issuance;
                })
                ->after(function ($record) {
                    // ── Transmittal notification ──────────────────────────────
                    if ($record->transmittal_id) {
                        $record->loadMissing('transmittal');
                        $txnNo = $record->transmittal?->transmittal_number;

                        Notification::make()
                            ->title("📮 Transmittal {$txnNo} Created")
                            ->body("Transmitted to: {$record->transmitted_to}")
                            ->success()
                            ->persistent()
                            ->send();
                    }

                    // ── Receiving copy notification ───────────────────────────
                    if (in_array($record->status, ['partial', 'issued'])) {
                        $url       = url("/receiving-copy/issuance/{$record->id}");
                        $isPartial = $record->status === 'partial';

                        Notification::make()
                            ->title($isPartial
                                ? '📋 Partial Issuance Created — Receiving Copy Ready'
                                : '✅ Issuance Created — Receiving Copy Ready'
                            )
                            ->body($isPartial
                                ? 'Created with status "Partial". You can print the receiving copy for this release.'
                                : 'Created with status "Issued". You can now print or download the receiving copy.'
                            )
                            ->success()
                            ->persistent()
                            ->actions([
                                Action::make('view_receiving_copy')
                                    ->label('🖨️ Open Receiving Copy')
                                    ->url($url, shouldOpenInNewTab: true)
                                    ->button(),
                            ])
                            ->send();
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        $counts = $this->getStatusCounts();

        return [
            'all' => Tab::make('All')->badge($counts['all'] ?? 0),

            'pending' => Tab::make('Pending')
                ->badge($counts['pending'] ?? 0)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),

            'partial' => Tab::make('Partial')
                ->badge($counts['partial'] ?? 0)->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'partial')),

            'issued' => Tab::make('Issued')
                ->badge($counts['issued'] ?? 0)->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'issued')),

            'returned' => Tab::make('Returned')
                ->badge($counts['returned'] ?? 0)->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned')),

            'cancelled' => Tab::make('Cancelled')
                ->badge($counts['cancelled'] ?? 0)->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}