<?php

namespace App\Filament\Crew\Resources\CrewDrafts\Pages;

use App\Filament\Crew\Resources\CrewDrafts\CrewDraftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrewDrafts extends ListRecords
{
    protected static string $resource = CrewDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
