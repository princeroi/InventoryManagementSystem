<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
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