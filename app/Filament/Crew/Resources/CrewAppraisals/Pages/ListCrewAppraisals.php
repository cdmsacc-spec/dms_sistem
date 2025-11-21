<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Pages;

use App\Filament\Crew\Resources\CrewAppraisals\CrewAppraisalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewAppraisals extends ListRecords
{
    protected static string $resource = CrewAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
