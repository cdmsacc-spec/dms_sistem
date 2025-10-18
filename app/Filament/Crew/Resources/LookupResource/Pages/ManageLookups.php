<?php

namespace App\Filament\Crew\Resources\LookupResource\Pages;

use App\Filament\Crew\Resources\LookupResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLookups extends ManageRecords
{
    protected static string $resource = LookupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
