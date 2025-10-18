<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginAdmin;
use App\Filament\Crew\Resources\AppraisalResource;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Filament\Crew\Resources\CrewDraftResource;
use App\Filament\Crew\Resources\InterviewResource;
use App\Filament\Crew\Resources\JabatanResource;
use App\Filament\Crew\Resources\LookupResource;
use App\Filament\Crew\Resources\MutasiPromosiResource;
use App\Filament\Crew\Resources\NamaKapalResource;
use App\Filament\Crew\Resources\PerusahaanResource;
use App\Filament\Crew\Resources\PklReminderResource;
use App\Filament\Crew\Resources\SignOffResource;
use App\Filament\Crew\Resources\SignOnResource;
use App\Filament\Crew\Resources\WilayahOperasionalResource;
use App\Filament\Document\Resources\DocumentReminderResource;
use App\Filament\Document\Resources\DocumentResource;
use App\Filament\Document\Resources\JenisDocumentResource;
use App\Filament\Resources\ActivityLogResource;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginAdmin::class)
            ->authGuard('web')
            ->userMenuItems([
                'logout' => MenuItem::make()->label('Log out'),
                'profile' => MenuItem::make()
                    ->visible(fn() => auth()->user()->can('page_EditProfilePage'))
                    ->label(fn() => auth()->user()->name)
                    ->url(fn(): string =>  EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
            ])
            ->colors([
                'info' => Color::hex('#3bc4ff'),
                'warning' => Color::hex('#EDBC1C'),
                'success' => Color::hex('#1CED23'),
                'danger' => Color::hex('#ED1C1C'),
                'primary' => Color::hex('#003366')
            ])
            ->resources([
                CrewDraftResource::class,
                InterviewResource::class,
                MutasiPromosiResource::class,
                SignOnResource::class,
                SignOffResource::class,
                CrewAllResource::class,
                AppraisalResource::class,
                PklReminderResource::class,

                DocumentResource::class,
                DocumentReminderResource::class,
                JenisDocumentResource::class,
                   

                NamaKapalResource::class,
                PerusahaanResource::class,
                JabatanResource::class,
                WilayahOperasionalResource::class
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->simplePageMaxContentWidth(MaxWidth::Small)
            ->spa()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ->plugins([
                FilamentShieldPlugin::make(),
                ActivitylogPlugin::make()->navigationGroup('Settings')
                    ->resource(ActivityLogResource::class),
                FilamentEditProfilePlugin::make()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'user/profile',
                        rules: 'mimes:jpeg,png'
                    )
                    ->canAccess(fn() => auth()->user()?->can('page_EditProfilePage'))
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm(false)
                    ->shouldRegisterNavigation(false),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/staff_crew/theme.css');
    }
}
