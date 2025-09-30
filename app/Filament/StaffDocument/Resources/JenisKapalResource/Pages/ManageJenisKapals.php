<?php

namespace App\Filament\StaffDocument\Resources\JenisKapalResource\Pages;

use App\Filament\StaffDocument\Resources\JenisKapalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisKapals extends ManageRecords
{
    protected static string $resource = JenisKapalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Data'),
        ];
    }
}
