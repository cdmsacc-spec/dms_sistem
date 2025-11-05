<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages\SubNavigation;

use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class Interview extends ManageRelatedRecords
{
    protected static string $resource = CrewAllResource::class;

    protected static string $relationship = 'crewInterview';
    protected static ?string $navigationIcon = '';
    protected static ?string $navigationLabel = 'Interview';
    protected static ?string $navigationGroup = 'Histori Crew';


    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('History Interview')
            ->recordTitleAttribute('tanggal')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('hasil_interviewe1')
                    ->label('Interview 1'),
                Tables\Columns\TextColumn::make('hasil_interviewe2')
                    ->label('Interview 2'),
                Tables\Columns\TextColumn::make('hasil_interviewe3')
                    ->label('Interview 3'),
                Tables\Columns\TextColumn::make('sumary')
                    ->label('Summary'),

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

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->color('success')
                    ->button()
                    ->modalWidth('100%')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Interview ' . $record->tanggal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file_path)))
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
