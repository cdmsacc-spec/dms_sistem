<?php

namespace App\Filament\Document\Resources\JenisDocumentResource\Pages;

use App\Filament\Document\Resources\JenisDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisDocuments extends ManageRecords
{
    protected static string $resource = JenisDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
