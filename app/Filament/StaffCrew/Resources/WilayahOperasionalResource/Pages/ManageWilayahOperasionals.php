<?php

namespace App\Filament\StaffCrew\Resources\WilayahOperasionalResource\Pages;

use App\Filament\StaffCrew\Resources\WilayahOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWilayahOperasionals extends ManageRecords
{
    protected static string $resource = WilayahOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Add Wilayah'),
        ];
    }
}
