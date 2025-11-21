<?php

namespace App\Filament\Crew\Resources\CrewMutasis\Pages;

use App\Filament\Crew\Resources\CrewMutasis\CrewMutasiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrewMutasi extends ViewRecord
{
    protected static string $resource = CrewMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
