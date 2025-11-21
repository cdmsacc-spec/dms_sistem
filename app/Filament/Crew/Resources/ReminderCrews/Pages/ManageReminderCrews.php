<?php

namespace App\Filament\Crew\Resources\ReminderCrews\Pages;

use App\Filament\Crew\Resources\ReminderCrews\ReminderCrewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReminderCrews extends ManageRecords
{
    protected static string $resource = ReminderCrewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New reminder'),
        ];
    }
}
