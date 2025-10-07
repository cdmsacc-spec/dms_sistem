<?php

namespace App\Filament\StaffDocument\Resources\LookupResource\Pages;

use App\Filament\StaffDocument\Resources\LookupResource;
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
