<?php

namespace App\Filament\Crew\Resources\NotificationResource\Pages;

use App\Filament\Crew\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotifications extends ManageRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
