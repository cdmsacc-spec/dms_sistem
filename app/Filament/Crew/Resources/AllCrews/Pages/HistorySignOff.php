<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class HistorySignOff extends ManageRelatedRecords
{
    protected static string $resource = AllCrewResource::class;

    protected static string $relationship = 'signoff';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('keterangan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('keterangan'),
                TextEntry::make('tanggal')
                    ->getStateUsing(function ($record) {
                        if (!$record) {
                            return '-';
                        }
                        return $record?->tanggal == null ? '-' : Carbon::parse($record?->tanggal)->format('d-M-Y');
                    })
                    ->icon('heroicon-m-calendar'),
                TextEntry::make('keterangan'),
                TextEntry::make('alasanBerhenti.nama_alasan')->label('Alasan Berhenti'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->recordTitleAttribute('keterangan')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('tanggal')
                    ->formatStateUsing(fn($record) => $record->tanggal ? Carbon::parse($record->tanggal)->format('d-M-Y') : 'Tidak Ada Tangal Expired'),
                TextColumn::make('keterangan'),
                TextColumn::make('alasanBerhenti.nama_alasan')->label('Alasan Berhenti'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('Preview')
                    ->label('Preview')
                    ->size('sm')
                    ->color('info')
                    ->button()
                    ->modalWidth('full')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Mutasi/Promosi ' . $record->nomor_dokumen)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                ViewAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
