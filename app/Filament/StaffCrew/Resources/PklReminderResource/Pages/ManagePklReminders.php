<?php

namespace App\Filament\StaffCrew\Resources\PklReminderResource\Pages;

use App\Filament\StaffCrew\Resources\PklReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePklReminders extends ManageRecords
{
    protected static string $resource = PklReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Reminder')->modalHeading('Create Reminder'),
        ];
    }
}
