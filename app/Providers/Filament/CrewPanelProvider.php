<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfiles;
use App\Filament\Auth\CrewLogin;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\JenisKapals\JenisKapalResource;
use App\Filament\Resources\Kapals\KapalResource;
use App\Filament\Resources\Lookups\LookupResource;
use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Filament\Resources\WilayahOperasionals\WilayahOperasionalResource;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DateWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;

class CrewPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('crew')
            ->path('crew')
            ->login(CrewLogin::class)
            ->spa()
            ->databaseNotifications()
            ->userMenuItems([
                'profile' => fn(Action $action) => $action
                    ->visible(fn() => auth()->user()->can('view:edit-profiles'))
                    ->label(fn() => auth()->user()->name)
                    ->url(fn(): string => EditProfiles::getUrl())
                    ->icon('heroicon-m-user-circle')
            ])
            ->colors([
                'primary' =>  Color::Blue,
            ])
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->simplePageMaxContentWidth(Width::Small)
            ->resources([
                JabatanResource::class,
                JenisKapalResource::class,
                KapalResource::class,
                WilayahOperasionalResource::class,
                PerusahaanResource::class,
                LookupResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Crew/Resources'), for: 'App\Filament\Crew\Resources')
            ->discoverPages(in: app_path('Filament/Crew/Pages'), for: 'App\Filament\Crew\Pages')
            ->pages([
                EditProfiles::class
            ])
            ->discoverWidgets(in: app_path('Filament/Crew/Widgets'), for: 'App\Filament\Crew\Widgets')
            ->widgets([
                AccountWidget::class,
                DateWidget::class,
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
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentAuthenticationLogPlugin::make()
            ])
              ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
