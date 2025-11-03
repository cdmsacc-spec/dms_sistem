<?php

namespace App\Filament\Crew\Resources\EmailReminderPklResource\Pages;

use App\Filament\Crew\Resources\EmailReminderPklResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEmailReminderPkls extends ManageRecords
{
    protected static string $resource = EmailReminderPklResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Email Reminder')->modalHeading('Create Email Reminder'),
        ];
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable{
        return 'Email Reminder';
    }
}
