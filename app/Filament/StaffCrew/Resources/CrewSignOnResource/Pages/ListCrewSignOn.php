<?php

namespace App\Filament\StaffCrew\Resources\CrewSignOnResource\Pages;

use App\Filament\StaffCrew\Resources\CrewSignOnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewSignOn extends ListRecords
{
    protected static string $resource = CrewSignOnResource::class;
    protected static ?string $title = 'Sign On';
    protected function getHeaderActions(): array
    {
        return [];
    }
}
