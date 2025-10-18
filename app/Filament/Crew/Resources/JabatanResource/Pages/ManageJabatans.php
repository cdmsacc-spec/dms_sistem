<?php

namespace App\Filament\Crew\Resources\JabatanResource\Pages;

use App\Filament\Crew\Resources\JabatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJabatans extends ManageRecords
{
    protected static string $resource = JabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
