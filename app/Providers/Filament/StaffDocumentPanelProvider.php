<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginStaffDocument;
use App\Filament\StaffDocument\Resources\ActivityLogResource;
use App\Http\Middleware\RedirectToProperPanelMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rmsramos\Activitylog\ActivitylogPlugin;

class StaffDocumentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->viteTheme('resources/css/filament/staff_crew/theme.css')
            ->id('staff_document')
            ->path('staff_document')
            ->login(LoginStaffDocument::class)
            ->colors([
                'info' => Color::hex('#3bc4ff'),
                'warning' => Color::hex('#EDBC1C'),
                'success' => Color::hex('#1CED23'),
                'danger' => Color::hex('#ED1C1C'),
                'primary' => Color::hex('#3bc4ff')
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->simplePageMaxContentWidth(MaxWidth::Small)
            ->unsavedChangesAlerts()
            ->spa()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/StaffDocument/Resources'), for: 'App\\Filament\\StaffDocument\\Resources')
            ->discoverPages(in: app_path('Filament/StaffDocument/Pages'), for: 'App\\Filament\\StaffDocument\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/StaffDocument/Widgets/Dashboard'), for: 'App\\Filament\\StaffDocument\\Widgets\\Dashboard')
            ->widgets([])
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
            ->font('Outfit')
            ->brandName('DMS')
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                ActivitylogPlugin::make()->navigationGroup('Settings')
                    ->resource(ActivityLogResource::class),
            ]);
    }
}
