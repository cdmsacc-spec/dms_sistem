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

class Promosi extends ManageRelatedRecords
{
    protected static string $resource = CrewAllResource::class;

    protected static string $relationship = 'crewPkl';
    protected static ?string $navigationIcon = '';
    protected static ?string $navigationLabel = 'Mutasi Promosi';
    protected static ?string $navigationGroup = 'Histori Crew';
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('kategory', 'Promosi'))
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->heading('History Mutasi Promosi')
            ->recordTitleAttribute('nomor_document')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')
                    ->badge()
                    ->color('success')
                    ->label('Nomor'),
                Tables\Columns\TextColumn::make('perusahaan.kode_perusahaan'),
                Tables\Columns\TextColumn::make('jabatan.kode_jabatan'),
                Tables\Columns\TextColumn::make('kontrak_lanjutan')
                    ->label('Jenis Kontrak')
                    ->formatStateUsing(fn($state) => $state == true ? 'Lanjutan' : 'Baru'),
                Tables\Columns\TextColumn::make('start_date'),
                Tables\Columns\TextColumn::make('end_date'),
                Tables\Columns\TextColumn::make('status_kontrak')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Active' => 'success',
                        'Waiting Approval' => 'info',
                        'Expired' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('end_date'),
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
                    ->modalHeading(fn($record) => 'Mutasi / Promosi ' . $record->nomor_document)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file_path)))
                    ->visible(function ($record) {
                        $path = $record->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                Tables\Actions\Action::make('Detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => CrewAllResource::getUrl('detail_pkl', ['record' => $record->id]))
                    ->openUrlInNewTab(false),
                    
                Tables\Actions\DeleteAction::make()->button(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
