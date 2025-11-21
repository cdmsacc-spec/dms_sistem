<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginAdmin;
use App\Filament\Crew\Resources\AlasanBerhentis\AlasanBerhentiResource;
use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Filament\Crew\Resources\CrewAppraisals\CrewAppraisalResource;
use App\Filament\Crew\Resources\CrewDrafts\CrewDraftResource;
use App\Filament\Crew\Resources\CrewInterviews\CrewInterviewResource;
use App\Filament\Crew\Resources\CrewMutasis\CrewMutasiResource;
use App\Filament\Crew\Resources\CrewSignoffs\CrewSignoffResource;
use App\Filament\Crew\Resources\CrewSignOns\CrewSignOnResource;
use App\Filament\Crew\Resources\ReminderCrews\ReminderCrewResource;
use  App\Filament\Crew\Resources\ToReminderCrews\ToReminderCrewResource;
use App\Filament\Crew\Widgets\CrewJabatanGroup;
use App\Filament\Crew\Widgets\CrewUsiaGroup;
use App\Filament\Crew\Widgets\CrewAnalytic;
use App\Filament\Crew\Widgets\CrewActivityBerjalan;
use App\Filament\Crew\Widgets\DokumenCrewNearExpiry;
use App\Filament\Crew\Widgets\KontrakCrewNearExpiry;
use App\Filament\Crew\Widgets\SertifikatCrewNearExpiry;
use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Widgets\DokumenAnalytic;
use App\Filament\Document\Widgets\StatusAllDokumen;
use App\Filament\Document\Widgets\StatusDokumen;
use App\Filament\Document\Widgets\DokumentNearExpired;
use App\Filament\Pages\EditProfiles;
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
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DateWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginAdmin::class)
            ->spa()
            ->userMenuItems([
                'profile' => fn(Action $action) => $action
                    ->visible(fn() => auth()->user()->can('view:edit-profiles'))
                    ->label(fn() => auth()->user()->name)
                    ->url(fn(): string => EditProfiles::getUrl())
                    ->icon('heroicon-m-user-circle')
            ])
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->simplePageMaxContentWidth(Width::Small)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                DateWidget::class,
                DokumenAnalytic::class,
                StatusAllDokumen::class,
                StatusDokumen::class,

                CrewAnalytic::class,
                CrewActivityBerjalan::class,
                CrewJabatanGroup::class,
                CrewUsiaGroup::class,
                DokumentNearExpired::class,
                DokumenCrewNearExpiry::class,
                SertifikatCrewNearExpiry::class,
                KontrakCrewNearExpiry::class,
            ])
            ->resources([
                AlasanBerhentiResource::class,
                ReminderCrewResource::class,
                ToReminderCrewResource::class,
                AllCrewResource::class,
                CrewDraftResource::class,
                CrewInterviewResource::class,
                CrewSignOnResource::class,
                CrewAppraisalResource::class,
                CrewMutasiResource::class,
                CrewSignoffResource::class,

                DokumenResource::class,
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
                FilamentAuthenticationLogPlugin::make(),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
