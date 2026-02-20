<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function newQuery()
    {
        return parent::newQuery()
            ->when(
                function_exists('filament') && filament()->hasTenancy(),
                fn ($q) => $q->withoutGlobalScope(filament()->getTenancyScopeName())
            );
    }
}