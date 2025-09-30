<?php

namespace App\Filament\StaffDocument\Resources\DocumentResource\Pages;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Models\DocumentHistorie;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
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
