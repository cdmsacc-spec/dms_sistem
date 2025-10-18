<?php

namespace App\Filament\Document\Resources\JenisKapalResource\Pages;

use App\Filament\Document\Resources\JenisKapalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisKapals extends ManageRecords
{
    protected static string $resource = JenisKapalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
