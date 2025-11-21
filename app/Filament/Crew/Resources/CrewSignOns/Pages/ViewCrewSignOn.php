<?php

namespace App\Filament\Crew\Resources\CrewSignOns\Pages;

use App\Filament\Crew\Resources\CrewSignOns\CrewSignOnResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrewSignOn extends ViewRecord
{
    protected static string $resource = CrewSignOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
