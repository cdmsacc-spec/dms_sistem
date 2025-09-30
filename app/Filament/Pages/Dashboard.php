<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverviewDashboard;
use App\Filament\StaffDocument\Widgets\Dashboard\TabelExpiredDashboard;
use App\Filament\Widgets\BarChartDashboard;
use App\Filament\Widgets\CrewAnalyticStats;
use App\Filament\Widgets\CrewPerjabatan;
use App\Filament\Widgets\MutasiBerjalan;
use App\Filament\Widgets\PenggolonganUsia;
use App\Filament\Widgets\PieChartDashboard;
use App\Filament\Widgets\StatsOverviewUserResource;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverviewUserResource::class,
            StatsOverviewDashboard::class,
            PieChartDashboard::class,
            BarChartDashboard::class,
            CrewAnalyticStats::class,
            CrewPerjabatan::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 4;
    }
    public function getFooterWidgets(): array
    {
        return [
            MutasiBerjalan::class,
            PenggolonganUsia::class,
        ];
    }
}
