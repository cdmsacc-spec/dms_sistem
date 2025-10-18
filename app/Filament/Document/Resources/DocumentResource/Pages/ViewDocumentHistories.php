<?php

namespace App\Filament\Document\Resources\DocumentResource\Pages;

use App\Filament\Document\Resources\DocumentResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ViewDocumentHistories extends ListActivities
{
    protected static string $resource = DocumentResource::class;
    protected static ?string $title = 'History Document';
    public function getBreadcrumbs(): array
    {
        return [
            DocumentResource::getUrl('index') => 'Document',
            ViewDocument::getUrl(['record' => $this->record]) => 'View',
            null => 'History',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ViewDocument::getUrl(['record' => $this->record]);
    }
}
