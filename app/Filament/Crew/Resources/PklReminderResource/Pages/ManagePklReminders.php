<?php

namespace App\Filament\Crew\Resources\PklReminderResource\Pages;

use App\Filament\Crew\Resources\PklReminderResource;
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

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Reminder';
    }
}
