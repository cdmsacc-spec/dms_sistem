<?php

namespace App\Filament\StaffCrew\Resources\ActivityLogResource\Pages;

use App\Filament\StaffCrew\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateActivityLog extends CreateRecord
{
    protected static string $resource = ActivityLogResource::class;
}
