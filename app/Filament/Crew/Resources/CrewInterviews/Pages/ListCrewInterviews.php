<?php

namespace App\Filament\Crew\Resources\CrewInterviews\Pages;

use App\Filament\Crew\Resources\CrewInterviews\CrewInterviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewInterviews extends ListRecords
{
    protected static string $resource = CrewInterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
