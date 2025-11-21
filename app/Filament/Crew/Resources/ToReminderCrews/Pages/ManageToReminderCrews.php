<?php

namespace App\Filament\Crew\Resources\ToReminderCrews\Pages;

use App\Filament\Crew\Resources\ToReminderCrews\ToReminderCrewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageToReminderCrews extends ManageRecords
{
    protected static string $resource = ToReminderCrewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New reminder to'),
        ];
    }
}
