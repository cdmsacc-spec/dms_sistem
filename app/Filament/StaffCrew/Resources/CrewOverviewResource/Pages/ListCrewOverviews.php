<?php

namespace App\Filament\StaffCrew\Resources\CrewOverviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewOverviewResource\Widget\CrewOverviewStats;
use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewOverviews extends ListRecords
{
    protected static string $resource = CrewOverviewResource::class;
    protected static ?string $title = 'All Crew Overview';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CrewOverviewStats::class,
        ];
    }

  
}
