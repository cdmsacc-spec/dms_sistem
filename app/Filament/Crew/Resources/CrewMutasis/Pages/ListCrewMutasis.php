<?php

namespace App\Filament\Crew\Resources\CrewMutasis\Pages;

use App\Filament\Crew\Resources\CrewMutasis\CrewMutasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewMutasis extends ListRecords
{
    protected static string $resource = CrewMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
