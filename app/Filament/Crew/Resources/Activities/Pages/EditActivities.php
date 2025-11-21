<?php

namespace App\Filament\Crew\Resources\Activities\Pages;

use App\Filament\Crew\Resources\Activities\ActivitiesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActivities extends EditRecord
{
    protected static string $resource = ActivitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
       
        ];
    }
}
