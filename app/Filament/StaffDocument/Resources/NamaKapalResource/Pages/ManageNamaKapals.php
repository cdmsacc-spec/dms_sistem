<?php

namespace App\Filament\StaffDocument\Resources\NamaKapalResource\Pages;

use App\Filament\StaffDocument\Resources\NamaKapalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNamaKapals extends ManageRecords
{
    protected static string $resource = NamaKapalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Data'),
        ];
    }
}
