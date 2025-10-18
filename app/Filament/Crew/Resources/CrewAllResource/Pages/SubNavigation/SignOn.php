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
use Filament\Tables\Actions\Action;

class SignOn extends ManageRelatedRecords
{
    protected static string $resource = CrewAllResource::class;

    protected static string $relationship = 'crewPkl';
    protected static ?string $navigationIcon = '';
    protected static ?string $navigationLabel = 'Sign On';
    protected static ?string $navigationGroup = 'Histori Crew';


    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('kategory', 'Sign On'))
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->heading('History Sign On')
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
                Tables\Actions\Action::make('Detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => CrewAllResource::getUrl('detail_pkl', ['record' => $record->id]))
                    ->openUrlInNewTab(false)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
