<?php

namespace App\Filament\Pages;

use App\Filament\Crew\Widgets\CrewActivityBerjalan;
use App\Filament\Crew\Widgets\CrewAnalytic;
use App\Filament\Crew\Widgets\CrewJabatanGroup;
use App\Filament\Crew\Widgets\CrewUsiaGroup;
use App\Filament\Crew\Widgets\DokumenCrewNearExpiry;
use App\Filament\Crew\Widgets\KontrakCrewNearExpiry;
use App\Filament\Crew\Widgets\SertifikatCrewNearExpiry;
use App\Filament\Document\Widgets\DokumenAnalytic;
use App\Filament\Document\Widgets\DokumentNearExpired;
use App\Filament\Document\Widgets\StatusAllDokumen;
use App\Filament\Document\Widgets\StatusDokumen;
use BackedEnum;
use Filament\Pages\Dashboard as PagesDashboard;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DateWidget;

class Dashboard extends PagesDashboard
{
    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-squares-2x2';
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return ["sm" => 1, "md" => 1, "lg" => 1, "xl" => 2];
    }
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            DateWidget::class,
        ];
    }
    public function getColumns(): array|int
    {
        return 4;
    }
    public function getWidgets(): array
    {
        return [
            DokumenAnalytic::class,
            StatusAllDokumen::class,
            StatusDokumen::class,

            CrewAnalytic::class,
            CrewActivityBerjalan::class,
            CrewUsiaGroup::class,
            CrewJabatanGroup::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [

            DokumentNearExpired::class,
            DokumenCrewNearExpiry::class,
            SertifikatCrewNearExpiry::class,
            KontrakCrewNearExpiry::class,
        ];
    }
}
