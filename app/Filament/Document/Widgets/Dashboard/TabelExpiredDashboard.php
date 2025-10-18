<?php

namespace App\Filament\Document\Widgets\Dashboard;

use App\Enums\StatusDocumentFile;
use App\Filament\Document\Resources\DocumentResource;
use App\Models\Document;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TabelExpiredDashboard extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 4;

    protected static string $view = 'filament.staff-document.widgets.dokumen-near';
    protected static ?string $heading = 'Document Near Expiry';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()->where('status', StatusDocumentFile::NearExpiry)
            )
            ->heading('')
            ->defaultGroup('kapal.perusahaan.kode_perusahaan')
            ->groups([
                Group::make('kapal.perusahaan.kode_perusahaan')
                    ->label('Perusahaan')
                    ->collapsible(),
            ])
            ->groupingSettingsHidden()
            ->actions([
                Action::make('view')
                    ->color('success')
                    ->visible(auth()->user()?->can('view_any_document'))
                    ->button()
                    ->url(fn($record) => DocumentResource::getUrl('view', ['record' => $record])),

            ])
            ->filters([
                SelectFilter::make('perusahaan')
                    ->label('Perusahaan')
                    ->native(false)
                    ->relationship('kapal.perusahaan', 'nama_perusahaan')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_perusahaan)
                    ->preload()
            ])
            ->columns([
                Tables\Columns\TextColumn::make('kapal.perusahaan.nama_perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisDocument.nama_dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latestExpiration.tanggal_expired')
                    ->label('Expired')
                    ->badge()
                    ->color('danger'),
            ]);
    }
}
