<?php

namespace App\Filament\Crew\Resources\CrewInterviews\Pages;

use App\Filament\Crew\Resources\CrewInterviews\CrewInterviewResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrewInterview extends ViewRecord
{
    protected static string $resource = CrewInterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
