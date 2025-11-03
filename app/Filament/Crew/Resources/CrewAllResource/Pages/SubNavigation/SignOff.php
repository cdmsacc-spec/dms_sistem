<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages\SubNavigation;

use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

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
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->size('sm')
                    ->color('success')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file_path), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('priview')
                    ->label('Priview')
                    ->size('sm')
                    ->color('success')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Sign Off ' . $record->tanggal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file_path)))
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
