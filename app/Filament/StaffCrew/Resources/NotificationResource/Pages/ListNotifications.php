<?php

namespace App\Filament\StaffCrew\Resources\NotificationResource\Pages;

use App\Filament\StaffCrew\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
