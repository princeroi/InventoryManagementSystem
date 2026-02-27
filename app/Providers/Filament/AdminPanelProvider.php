<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\RecentIssuancesWidget;
use App\Filament\Widgets\RecentRestocksWidget;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Livewire\NewSupplyRequestAlert;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Moataz01\FilamentNotificationSound\FilamentNotificationSoundPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->globalSearch(false)
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->tenant(Department::class, slugAttribute: 'slug')
            ->login()
            ->login()
            ->homeUrl(fn () => auth()->user()?->hasRole('employee')
                ? '/admin/officesupply/office-supply-pos'
                : '/admin'
            )
            ->authGuard('web')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->plugin(
                FilamentNotificationSoundPlugin::make()
                    ->soundPath('/sounds/notification.mp3')
                    ->volume(1)
                    ->showAnimation(true)
                    ->enabled(true)
            )
            ->databaseNotifications()               
            ->databaseNotificationsPolling('2s')
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => Blade::render(<<<'HTML'
                    <script>
                        window.addEventListener('notification-received', () => {
                            console.log('Notification sound triggered!');
                            const audio = new Audio('/sounds/custom-notification.mp3');
                            audio.volume = 1.0;
                            audio.play()
                                .then(() => console.log('Sound played successfully'))
                                .catch(e => console.error('Sound play failed:', e));
                        });

                        // Listen for Filament/Livewire notification events
                        document.addEventListener('livewire:initialized', () => {
                            Livewire.hook('commit', ({ succeed }) => {
                                succeed(({ effect }) => {
                                    // Rough check for new notifications
                                    if (effect?.dispatches?.some(d => d.name.includes('notification')) ||
                                        document.querySelector('.fi-notifications-badge')) {
                                        window.dispatchEvent(new Event('notification-received'));
                                    }
                                });
                            });
                        });
                    </script>
                HTML)
            );
            
    }
}