<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages\SubNavigation;

use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true)

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
