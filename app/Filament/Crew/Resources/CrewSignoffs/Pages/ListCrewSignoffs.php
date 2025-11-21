<?php

namespace App\Filament\Crew\Resources\CrewSignoffs\Pages;

use App\Filament\Crew\Resources\CrewSignoffs\CrewSignoffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewSignoffs extends ListRecords
{
    protected static string $resource = CrewSignoffResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
