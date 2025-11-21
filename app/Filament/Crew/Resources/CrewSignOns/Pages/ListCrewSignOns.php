<?php

namespace App\Filament\Crew\Resources\CrewSignOns\Pages;

use App\Filament\Crew\Resources\CrewSignOns\CrewSignOnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewSignOns extends ListRecords
{
    protected static string $resource = CrewSignOnResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
