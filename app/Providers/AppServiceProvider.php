<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Role::resolveRelationUsing('department', fn () => null);
        Permission::resolveRelationUsing('department', fn () => null);

        Event::listen(Login::class, function ($event) {
            if ($event->user->hasRole('employee')) {
                session(['url.intended' => '/admin/officesupply/office-supply-pos']);
            }
        });
    }
}