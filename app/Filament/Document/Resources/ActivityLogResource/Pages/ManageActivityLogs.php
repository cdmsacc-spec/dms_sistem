<?php

namespace App\Filament\Document\Resources\ActivityLogResource\Pages;

use App\Filament\Document\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageActivityLogs extends ManageRecords
{
    protected static string $resource = ActivityLogResource::class;
}
