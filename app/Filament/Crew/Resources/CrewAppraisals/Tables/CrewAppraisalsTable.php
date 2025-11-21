<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Tables;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CrewAppraisalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn($query) => $query->where('status_kontrak', 'active')->withAvg('appraisal', 'nilai'))

            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('crew.nama_crew')
                    ->searchable(),
                TextColumn::make('perusahaan.kode_perusahaan')
                    ->searchable(),
                TextColumn::make('jabatan.kode_jabatan')
                    ->searchable(),
                TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status_kontrak')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                TextColumn::make('appraisal_avg_nilai')
                    ->badge()
                    ->description(fn($state) => match (true) {
                        $state >= 100 => 'Sangat memuaskan ',
                        $state >= 75  => 'Memuaskan',
                        $state >= 50  => 'Cukup memuaskan',
                        $state >= 25  => 'Tidak memuaskan',
                        default => 'gray',
                    })
                    ->formatStateUsing(
                        fn($state) => $state ? number_format($state, 0) : '-'
                    )
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75  => 'primary',
                        $state >= 50  => 'warning',
                        $state >= 25  => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('file')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file ? asset('storage/' . $record->file) : null, shouldOpenInNewTab: true),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->button()
                    ->url(fn($record) => AllCrewResource::getUrl('detail_kontrak', ['record' => $record->id])),

                EditAction::make()
                    ->button()
                    ->color('info')
                    ->label('Appraisal'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
