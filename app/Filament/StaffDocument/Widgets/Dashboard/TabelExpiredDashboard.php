<?php

namespace App\Filament\StaffDocument\Widgets\Dashboard;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Models\Document;
use App\Models\User;
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
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 4;
    protected static ?string $heading = 'Upcoming Expired Document';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()->where('status', 'Near Expiry')
            )
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
                    ->url(fn($record) => DocumentResource::getUrl('view', ['record' => $record])),

            ])
            ->filters([
                SelectFilter::make('perusahaan')
                    ->label('Perusahaan')
                    ->native(false)
                    ->relationship('kapal.perusahaan', 'nama_nama_perusahaan')
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'UpToDate'    => 'success',
                        'Near Expiry' => 'warning',
                        default       => 'danger',
                    }),
            ]);
    }
}

