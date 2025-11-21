<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Pages;

use App\Filament\Crew\Resources\CrewAppraisals\CrewAppraisalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrewAppraisal extends ViewRecord
{
    protected static string $resource = CrewAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
