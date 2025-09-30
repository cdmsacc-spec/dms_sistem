<?php

namespace App\Filament\StaffDocument\Resources\JenisDocumentResource\Pages;

use App\Filament\StaffDocument\Resources\JenisDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;

class ManageJenisDocuments extends ManageRecords
{
    protected static string $resource = JenisDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Data'),
        ];
    }
}
