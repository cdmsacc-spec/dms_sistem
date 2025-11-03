<?php

namespace App\Filament\Document\Widgets;

use App\Enums\StatusDocumentFile;
use App\Filament\Document\Resources\DocumentResource;
use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DocumentExpiredDashboard extends BaseWidget
{
    protected int | string | array $columnSpan = 4;
    protected static ?string $heading = 'Document Near Expiry';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()->where('status', StatusDocumentFile::Expired)
            )
            ->heading('')
            ->actions([
                Action::make('view')
                    ->color('success')
                    ->visible(auth()->user()?->can('view_any_document'))
                    ->button()
                    ->url(fn($record) => DocumentResource::getUrl('view', ['record' => $record])),

            ])
            ->columns([
                Tables\Columns\TextColumn::make('kapal.perusahaan.nama_perusahaan'),
                Tables\Columns\TextColumn::make('kapal.nama_kapal'),
                Tables\Columns\TextColumn::make('jenisDocument.nama_dokumen'),
                Tables\Columns\TextColumn::make('latestExpiration.nomor_dokumen'),
                Tables\Columns\TextColumn::make('latestExpiration.tanggal_expired')
                    ->label('Expired')
                    ->badge()
                    ->color('danger'),
            ]);
    }
}
