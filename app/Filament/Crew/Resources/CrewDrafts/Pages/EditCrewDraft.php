<?php

namespace App\Filament\Crew\Resources\CrewDrafts\Pages;

use App\Filament\Crew\Resources\CrewDrafts\CrewDraftResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCrewDraft extends EditRecord
{
    protected static string $resource = CrewDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
