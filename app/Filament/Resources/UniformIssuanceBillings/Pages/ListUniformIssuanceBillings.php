<?php

namespace App\Filament\Resources\UniformIssuanceBillings\Pages;

use App\Filament\Resources\UniformIssuanceBillings\UniformIssuanceBillingResource;
use App\Models\UniformIssuanceBilling;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListUniformIssuanceBillings extends ListRecords
{
    protected static string $resource = UniformIssuanceBillingResource::class;

    // ── No header Create button — billing records are auto-generated ──────
    protected function getHeaderActions(): array
    {
        return [];
    }

    // ── Tenant-scoped base query ──────────────────────────────────────────
    // Billing records link to issuances which carry the department_id (tenant).
    // We also gate the query so users without view permission see nothing.
    protected function getTableQuery(): Builder
    {
        if (! Auth::user()?->can('view-any uniform-issuance-billing')) {
            // Return an empty result set rather than throwing a 403
            return UniformIssuanceBilling::query()->whereRaw('1 = 0');
        }

        $tenantId = Filament::getTenant()?->id;

        return UniformIssuanceBilling::query()
            ->whereHas(
                'uniformIssuance',
                fn (Builder $q) => $q->where('department_id', $tenantId)
            )
            ->with([
                'uniformIssuance.site',
                'uniformIssuance.issuanceType',
                'recipient.items.item',
            ]);
    }

    // ── Tab badge counts (also tenant-scoped) ─────────────────────────────
    protected function getStatusCounts(): array
    {
        $tenantId = Filament::getTenant()?->id;

        $rows = UniformIssuanceBilling::query()
            ->whereHas(
                'uniformIssuance',
                fn (Builder $q) => $q->where('department_id', $tenantId)
            )
            ->select('bill_status', DB::raw('count(*) as total'))
            ->groupBy('bill_status')
            ->pluck('total', 'bill_status')
            ->toArray();

        return array_merge(['all' => array_sum($rows)], $rows);
    }

    // ── Tabs ──────────────────────────────────────────────────────────────
    public function getTabs(): array
    {
        $counts = $this->getStatusCounts();

        return [
            'all' => Tab::make('All')
                ->badge($counts['all'] ?? 0),

            'pending' => Tab::make('Pending')
                ->badge($counts['pending'] ?? 0)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bill_status', 'pending')),

            'billed' => Tab::make('Billed')
                ->badge($counts['billed'] ?? 0)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bill_status', 'billed')),

            'not_billable' => Tab::make('Not Billable')
                ->badge($counts['not_billable'] ?? 0)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bill_status', 'not_billable')),
        ];
    }
}