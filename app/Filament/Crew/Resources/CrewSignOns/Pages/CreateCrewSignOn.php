<?php

namespace App\Filament\Crew\Resources\CrewSignOns\Pages;

use App\Filament\Crew\Resources\CrewSignOns\CrewSignOnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCrewSignOn extends CreateRecord
{
    protected static string $resource = CrewSignOnResource::class;

    public function mount(): void
    {
        parent::mount();
        $crewId = request()->query('id_crew');
        $this->crewId = $crewId;
    }
}
