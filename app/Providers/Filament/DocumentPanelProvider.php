<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginStaffDocument;
use App\Filament\Document\Resources\ActivityLogResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
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

class DocumentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('document')
            ->path('document')
            ->login(LoginStaffDocument::class)
            ->userMenuItems([
                'logout' => MenuItem::make()->label('Log out'),
                'profile' => MenuItem::make()
                    ->visible(fn() => auth()->user()->can('page_EditProfilePage'))
                    ->label(fn() => auth()->user()->name)
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
            ])
            ->colors([
                'info' => Color::hex('#3bc4ff'),
                'warning' => Color::hex('#EDBC1C'),
                'success' => Color::hex('#1CED23'),
                'danger' => Color::hex('#ED1C1C'),
                'primary' => Color::hex('#003366')
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->simplePageMaxContentWidth(MaxWidth::Small)
            ->spa()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Document/Resources'), for: 'App\\Filament\\Document\\Resources')
            ->discoverPages(in: app_path('Filament/Document/Pages'), for: 'App\\Filament\\Document\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Document/Widgets'), for: 'App\\Filament\\Document\\Widgets')
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
                        directory: 'public/user/profile',
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
