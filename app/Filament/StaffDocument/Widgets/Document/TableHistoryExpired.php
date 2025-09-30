<?php

namespace App\Filament\StaffDocument\Widgets\Document;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\DocumentExpiration;

class TableHistoryExpired extends BaseWidget
{
    public ?int $documentId = null;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return DocumentExpiration::query()
            ->where('document_id', $this->documentId);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('tanggal_terbit')->label('Tanggal Terbit'),
            TextColumn::make('tanggal_expired')->label('Tanggal Expired'),
            TextColumn::make('file_path')->label('File Dokumen')
                ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, true)
                ->formatStateUsing(fn($state) => $state ? 'Download File' : 'Tidak ada file'),
        ];
    }
}
