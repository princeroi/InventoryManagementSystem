<?php

namespace App\Filament\Pages;

use App\Models\Item;
use App\Models\OfficeSupplyRequest;
use App\Models\OfficeSupplyRequestItem;
use App\Models\User;
use App\Notifications\OfficeSupplyRequestedNotification;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\Width;

class OfficeSupplyPos extends Page
{
    protected string $view = 'filament.pages.office-supply-pos';
    protected static ?string $navigationLabel = 'Supply POS';
    protected static ?string $slug = 'office-supply-pos';
    public static function getNavigationGroup(): ?string
    {
        return 'Office Supplies';
    }

    private static function isOfficeSupplyTenant(): bool
    {
        return Filament::getTenant()?->slug === 'officesupply';
    }

    public static function canAccess(): bool
    {
        return self::isOfficeSupplyTenant();
    }

    public array $cart = [];
    public array $selectedVariants = [];

    public string $itemSearch = '';
    public string $activeCategory = 'All';
    public bool $showConfirm = false;
    public bool $submitted = false;
    public int $submittedRequestId = 0;
    public string $submittedEmployeeName = '';

    /* =========================================================
     |  COMPUTED PROPERTIES
     ========================================================= */

    public function getCurrentUserProperty(): ?array
    {
        $user = Auth::user();
        return $user ? ['id' => $user->id, 'name' => $user->name] : null;
    }

    public function getItemsProperty(): array
    {
        $departmentId = Filament::getTenant()?->id;

        return Item::with(['variants', 'category'])
            ->whereHas('departments', fn($q) => $q->where('departments.id', $departmentId))
            ->get()
            ->map(function ($item) {
                $variants = $item->variants->map(fn($v) => [
                    'id'       => $v->id,
                    'size'     => $v->size_label ?? 'Default',
                    'quantity' => (int) $v->quantity,
                ])->values()->toArray();

                return [
                    'id'       => $item->id,
                    'name'     => $item->name,
                    'category' => $item->category?->name ?? 'General',
                    'unit'     => $item->unit ?? 'pc',
                    'variants' => $variants,
                ];
            })
            ->toArray();
    }

    public function getCategoriesProperty(): array
    {
        $cats = collect($this->items)
            ->pluck('category')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return array_merge(['All'], $cats);
    }

    public function getFilteredItemsProperty(): array
    {
        return collect($this->items)
            ->when($this->activeCategory !== 'All', fn($c) => $c->where('category', $this->activeCategory))
            ->when($this->itemSearch, fn($c) => $c->filter(fn($i) => str_contains(strtolower($i['name']), strtolower($this->itemSearch))))
            ->values()
            ->toArray();
    }

    /* =========================================================
     |  CART KEY HELPER
     ========================================================= */

    private function getCartKey(int $itemId, ?int $variantId = null): string
    {
        return $variantId ? "{$itemId}-{$variantId}" : (string) $itemId;
    }

    /* =========================================================
     |  VARIANT HELPERS
     ========================================================= */

    private function getSelectedVariant(int $itemId): ?array
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item || empty($item['variants'])) return null;

        $key = (string) $itemId;
        $variantId = $this->selectedVariants[$key] ?? null;

        if ($variantId) {
            return collect($item['variants'])->firstWhere('id', $variantId);
        }

        return $item['variants'][0] ?? null;
    }

    private function getVariantQuantity(int $itemId, int $variantId): int
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item) return 0;

        $variant = collect($item['variants'])->firstWhere('id', $variantId);
        return (int) ($variant['quantity'] ?? 0);
    }

    /* =========================================================
     |  VARIANT CHANGE
     ========================================================= */

    public function updateVariant(int $itemId, int $variantId): void
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item) return;

        $variant = collect($item['variants'])->firstWhere('id', $variantId);
        if (!$variant) return;

        $key = (string) $itemId;
        $this->selectedVariants[$key] = $variantId;

        // If this item+variant combo is already in cart → update it
        $cartKey = $this->getCartKey($itemId, $variantId);
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['selected_variant_id'] = $variantId;
            $this->cart[$cartKey]['selected_size']       = $variant['size'];

            $available = (int) $variant['quantity'];
            $this->cart[$cartKey]['qty'] = min($this->cart[$cartKey]['qty'], max(1, $available));
        }
    }

    /* =========================================================
     |  CART ACTIONS
     ========================================================= */

    public function addToCart(int $itemId): void
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item) return;

        $variant = $this->getSelectedVariant($itemId);
        if (!$variant) return;

        $available = (int) $variant['quantity'];
        if ($available <= 0) return;

        $key = $this->getCartKey($itemId, $variant['id']);

        if (!isset($this->cart[$key])) {
            $this->cart[$key] = [
                'item'                => $item,
                'qty'                 => 1,
                'selected_variant_id' => $variant['id'],
                'selected_size'       => $variant['size'],
            ];
        } else {
            if ($this->cart[$key]['qty'] < $available) {
                $this->cart[$key]['qty']++;
            }
        }

        $this->cart = $this->cart; // force reactivity
    }

    public function incrementQty(string $cartKey): void
    {
        if (!isset($this->cart[$cartKey])) return;

        $itemId    = $this->cart[$cartKey]['item']['id'] ?? 0;
        $variantId = $this->cart[$cartKey]['selected_variant_id'] ?? null;

        $available = $variantId
            ? $this->getVariantQuantity($itemId, $variantId)
            : 999999;

        if ($this->cart[$cartKey]['qty'] < $available) {
            $this->cart[$cartKey]['qty']++;
        }
    }

    public function decrementQty(string $cartKey): void
    {
        if (!isset($this->cart[$cartKey])) return;

        if ($this->cart[$cartKey]['qty'] > 1) {
            $this->cart[$cartKey]['qty']--;
        } else {
            unset($this->cart[$cartKey]);
        }
    }

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    /* =========================================================
     |  SUBMIT FLOW
     ========================================================= */

    public function submit(): void
    {
        if (empty($this->cart)) return;
        $this->showConfirm = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirm = false;
    }

    public function confirmSubmit(): void
    {
        $user = Auth::user();
        if (!$user || empty($this->cart)) return;

        DB::transaction(function () use ($user) {
            $request = OfficeSupplyRequest::create([
                'department_id' => Filament::getTenant()?->id,
                'employee_id'   => $user->id,
                'requested_by'  => $user->id,
                'request_date'  => now()->toDateString(),
                'status'        => 'requested',
            ]);

            foreach ($this->cart as $cartItem) {
                OfficeSupplyRequestItem::create([
                    'office_supply_request_id' => $request->id,
                    'item_id'                  => $cartItem['item']['id'],
                    'item_variant_id'          => $cartItem['selected_variant_id'],
                    'quantity'                 => $cartItem['qty'],
                    'size_label'               => $cartItem['selected_size'],
                ]);

                if ($cartItem['selected_variant_id']) {
                    DB::table('item_variants')
                        ->where('id', $cartItem['selected_variant_id'])
                        ->decrement('quantity', $cartItem['qty']);
                }
            }

            $request->load('employee', 'items');

            User::permission('release office-supply-request')
                ->get()
                ->filter(fn ($user) => $user->departments()->where('slug', 'officesupply')->exists())
                ->each
                ->notify(new OfficeSupplyRequestedNotification($request));

            User::officeSupplyApprovers()
                ->each
                ->notify(new OfficeSupplyRequestedNotification($request));

            $this->submittedRequestId    = $request->id;
            $this->submittedEmployeeName = $user->name;
        });

        $this->cart        = [];
        $this->showConfirm = false;
        $this->submitted   = true;
    }

    public function resetPOS(): void
    {
        $this->submitted = false;
        $this->cart = [];
        $this->selectedVariants = [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}