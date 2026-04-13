<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDokumens extends ListRecords
{
    protected static string $resource = DokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('Create Dokumen')->icon('heroicon-o-pencil-square'),

            Action::make('createByKapal')
                ->label('Create by Kapal')
                ->icon('heroicon-o-squares-plus')
                ->color('success')
                ->url(DokumenResource::getUrl('create-by-kapal')),
        ];

        
    }
}
