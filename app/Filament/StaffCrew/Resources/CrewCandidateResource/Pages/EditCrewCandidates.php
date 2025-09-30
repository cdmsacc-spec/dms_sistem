<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\Pages;

use App\Filament\StaffCrew\Resources\CrewCandidateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrewCandidates extends EditRecord
{
    protected static string $resource = CrewCandidateResource::class;

     public function getTitle(): string
    {
        return 'Edit Data ' . $this->record->nama_crew;
    }
}
