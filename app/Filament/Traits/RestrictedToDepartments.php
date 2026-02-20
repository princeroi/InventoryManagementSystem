<?php

namespace App\Filament\Traits;

use Filament\Facades\Filament;

trait RestrictedToDepartments
{
    /**
     * Define the allowed department slugs in each resource.
     * Override this method in each resource that uses this trait.
     *
     * Example: return ['pharmacy', 'warehouse'];
     */
    protected static function allowedDepartments(): array
    {
        return []; // Default: hidden from all (override this!)
    }

    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();

        // Super admin can always see everything
        if (auth()->user()?->hasRole('super_admin')) {
            return true;
        }

        // No tenant context = hide the resource
        if (! $tenant) {
            return false;
        }

        // Only show if current dept slug is in the allowed list
        return in_array($tenant->slug, static::allowedDepartments());
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }
}
