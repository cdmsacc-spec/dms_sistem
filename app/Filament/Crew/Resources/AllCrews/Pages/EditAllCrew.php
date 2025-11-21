<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAllCrew extends EditRecord
{
    protected static string $resource = AllCrewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
