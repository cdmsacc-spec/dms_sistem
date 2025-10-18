<?php

namespace App\Filament\Document\Resources\LookupResource\Pages;

use App\Filament\Document\Resources\LookupResource;
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
