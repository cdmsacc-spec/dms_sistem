<?php

namespace App\Filament\Document\Resources\DocumentResource\Pages;

use App\Filament\Document\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use Filament\Resources\Pages\Page;

use Filament\Tables;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Columns\TextColumn;

class ViewDocumentExpiration extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = DocumentResource::class;
    protected static ?string $title = 'History Exparation';
    protected static string $view = 'filament.staff-document.resources.document-resource.pages.view-document-expiration';
    use InteractsWithRecord;


    // Mount record dari URL
    public function mount($record)
    {
        // Ambil model Document dari ID
        $this->record = \App\Models\Document::findOrFail($record);
    }

    // Query tabel untuk DocumentExpiration terkait
    protected function getTableQuery()
    {
        $data = DocumentExpiration::query()
            ->where('document_id', $this->record->id)
            ->latest();
        return $data;
    }

    // Definisikan kolom tabel
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('file_path')->label('File Document')
                ->icon('heroicon-o-document-text')
                ->formatStateUsing(fn($state) => $state ? 'Document File' : 'Tidak ada file')
                ->color(fn($state) => $state ? 'info' : 'danger')
                ->url(fn($state) => asset('storage/' . $state), shouldOpenInNewTab: true),
            TextColumn::make('tanggal_terbit')->label('Tanggal Terbit')
                ->badge()
                ->color('success'),
            TextColumn::make('tanggal_expired')->label('Tanggal Expired')
                ->badge()
                ->default('Tidak Ada Tangal Expired')
                ->color('danger'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            DocumentResource::getUrl('index') => 'Document',
            ViewDocument::getUrl(['record' => $this->record]) => 'View',
            null => 'exparation',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ViewDocument::getUrl(['record' => $this->record]);
    }
}
