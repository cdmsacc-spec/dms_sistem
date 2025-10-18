<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages;

use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrewAll extends EditRecord
{
    protected static string $resource = CrewAllResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
