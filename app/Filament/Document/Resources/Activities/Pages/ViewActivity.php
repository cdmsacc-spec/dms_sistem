<?php

namespace App\Filament\Document\Resources\Activities\Pages;

use App\Filament\Document\Resources\Activities\ActivityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
