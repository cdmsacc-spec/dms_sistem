<?php

namespace App\Filament\StaffDocument\Resources\DocumentReminderResource\Pages;

use App\Filament\StaffDocument\Resources\DocumentReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDocumentReminders extends ManageRecords
{
    protected static string $resource = DocumentReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
