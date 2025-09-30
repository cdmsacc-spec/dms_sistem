<?php

namespace App\Filament\StaffCrew\Resources\CrewOverviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCrewOverview extends CreateRecord
{
    protected static string $resource = CrewOverviewResource::class;

    protected function getFormActions(): array
    {
        return [
        ];
    }
}
