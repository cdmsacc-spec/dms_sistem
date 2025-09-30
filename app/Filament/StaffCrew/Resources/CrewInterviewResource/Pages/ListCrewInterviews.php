<?php

namespace App\Filament\StaffCrew\Resources\CrewInterviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewInterviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewInterviews extends ListRecords
{
    protected static string $resource = CrewInterviewResource::class;
    protected static ?string $title = 'Interview';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
