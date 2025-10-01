<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\Pages;

use App\Filament\StaffCrew\Resources\CrewCandidateResource;
use App\Models\CrewApplicants;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;


class ListCrewCandidates extends ListRecords
{
    protected static string $resource = CrewCandidateResource::class;
    protected static ?string $title = 'Candidates';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Candidate')->color('primary'),
        ];
    }
}
