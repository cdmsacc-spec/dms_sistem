<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Resources\Dokumens\RelationManagers\HistoryDokumenRelationManager;
use Filament\Resources\Pages\CreateRecord;

class CreateDokumen extends CreateRecord
{
    protected static string $resource = DokumenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_author'] = auth()->user()->id;
        $data['status'] = 'uptodate';
        return $data;
    }
    public static function getRelations(): array
    {
        return [
            HistoryDokumenRelationManager::class,
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
