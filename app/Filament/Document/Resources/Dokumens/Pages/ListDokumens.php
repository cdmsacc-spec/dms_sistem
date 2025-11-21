<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDokumens extends ListRecords
{
    protected static string $resource = DokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-pencil-square'),
        ];

        
    }
}
