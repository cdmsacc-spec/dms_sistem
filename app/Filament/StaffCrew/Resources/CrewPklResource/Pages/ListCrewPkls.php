<?php

namespace App\Filament\StaffCrew\Resources\CrewPklResource\Pages;

use App\Filament\StaffCrew\Resources\CrewPklResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewPkls extends ListRecords
{
    protected static string $resource = CrewPklResource::class;

    protected static ?string $title = 'Kontrak Pkl Active Crew';
}
