<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages\SubNavigation;
use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;


class SignOff extends ManageRelatedRecords
{
    protected static string $resource = CrewAllResource::class;

    protected static string $relationship = 'crewSignOff';
    protected static ?string $navigationIcon = '';
    protected static ?string $navigationLabel = 'Sign Off';
    protected static ?string $navigationGroup = 'Histori Crew';

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('History Sign Off')
            ->recordTitleAttribute('')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('tanggal'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
