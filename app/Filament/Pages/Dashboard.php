<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountWidgets;
use App\Filament\Widgets\Dashboard\BarChartDashboard;
use App\Filament\Widgets\Dashboard\CertificatesCrewExpired;
use App\Filament\Widgets\Dashboard\CrewAnalyticStats;
use App\Filament\Widgets\Dashboard\CrewPerjabatan;
use App\Filament\Widgets\Dashboard\DocumentCrewExpired;
use App\Filament\Widgets\Dashboard\KontrakCrewNearExpiry;
use App\Filament\Widgets\Dashboard\MutasiBerjalan;
use App\Filament\Widgets\Dashboard\PenggolonganUsia;
use App\Filament\Widgets\Dashboard\PieChartDashboard;
use App\Filament\Widgets\Dashboard\StatsOverviewDashboard;
use App\Filament\Widgets\Dashboard\TabelExpiredDashboard;
use App\Filament\Widgets\DateWidgets;
use App\Filament\Widgets\UserOverview;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';

    public function getColumns(): array|int|string
    {
        return 4;
    }

    public function getHeaderWidgets(): array
    {
        return [
            AccountWidgets::class,
            DateWidgets::class,

        ];
    }
    public function getWidgets(): array
    {
        return [
            UserOverview::class,
            BarChartDashboard::class,
            PieChartDashboard::class,
            StatsOverviewDashboard::class,
            TabelExpiredDashboard::class,
            CrewAnalyticStats::class,
            MutasiBerjalan::class,
            PenggolonganUsia::class,
            CrewPerjabatan::class,
            DocumentCrewExpired::class,
            CertificatesCrewExpired::class,
            KontrakCrewNearExpiry::class
        ];
    }
}
