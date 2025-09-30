<?php

namespace App\Filament\StaffDocument\Resources\NotificationResource\Pages;

use App\Filament\StaffDocument\Resources\NotificationResource;
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
