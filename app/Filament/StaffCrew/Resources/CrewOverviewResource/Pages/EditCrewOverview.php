<?php

namespace App\Filament\StaffCrew\Resources\CrewOverviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrewOverview extends EditRecord
{
    protected static string $resource = CrewOverviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
