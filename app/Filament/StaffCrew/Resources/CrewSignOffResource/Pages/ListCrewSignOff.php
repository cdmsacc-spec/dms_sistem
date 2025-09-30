<?php

namespace App\Filament\StaffCrew\Resources\CrewSignOffResource\Pages;

use App\Filament\StaffCrew\Resources\CrewSignOffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewSignOff extends ListRecords
{
    protected static string $resource = CrewSignOffResource::class;
    protected static ?string $title = 'Sign Off';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
