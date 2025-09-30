<?php

namespace App\Filament\StaffDocument\Resources\WilayahOperasionalResource\Pages;

use App\Filament\StaffDocument\Resources\WilayahOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWilayahOperasionals extends ManageRecords
{
    protected static string $resource = WilayahOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Data'),
        ];
    }
}
