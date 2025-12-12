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

class HistoryMutasiPromosi extends ManageRelatedRecords
{
    protected static string $resource = AllCrewResource::class;

    protected static string $relationship = 'mutasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public function getBreadcrumb(): string
    {
        return 'mutasi dan promosi ' . $this->record->nama_crew;
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('status_kontrak')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('status_kontrak'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->recordTitleAttribute('nomor_dokumen')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nomor_dokumen')
                    ->badge()
                    ->color('success')
                    ->label('Nomor'),
                TextColumn::make('perusahaan.kode_perusahaan'),
                TextColumn::make('jabatan.kode_jabatan'),
                TextColumn::make('kontrak_lanjutan')
                    ->label('Jenis Kontrak')
                    ->formatStateUsing(fn($state) => $state == true ? 'Lanjutan' : 'Baru'),
                TextColumn::make('start_date')
                    ->formatStateUsing(fn($record) => $record->start_date ? Carbon::parse($record->start_date)->format('d-M-Y') : '-'),
                TextColumn::make('end_date')
                    ->formatStateUsing(fn($record) => $record->end_date ? Carbon::parse($record->end_date)->format('d-M-Y') : '-'),

                TextColumn::make('status_kontrak')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active' => 'success',
                        'waiting approval' => 'info',
                        'expired' => 'danger',
                    }),
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

                Action::make('detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => AllCrewResource::getUrl('detail_kontrak', ['record' => $record->id]))
                    ->openUrlInNewTab(false),

                DeleteAction::make()->button()
                    ->before(function ($record) {
                        if ($record->status_kontrak == 'active') {
                            $record->crew->update(['status' => 'standby']);
                        }
                    }),
            ])
            ->toolbarActions([]);
    }
}
