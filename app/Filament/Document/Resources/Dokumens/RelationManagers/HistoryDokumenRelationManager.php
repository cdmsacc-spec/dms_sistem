<?php

namespace App\Filament\Document\Resources\Dokumens\RelationManagers;

use App\Filament\Document\Resources\Dokumens\Pages\EditDokumen;
use App\Models\JenisDokumen;
use App\Models\Kapal;
use App\Models\Perusahaan;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class HistoryDokumenRelationManager extends RelationManager
{
    protected static string $relationship = 'historyDokumen';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $pageClass === EditDokumen::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_terbit')
                    ->label('Tanggal Terbit')
                    ->live()
                    ->required()
                    ->native(false),
                DatePicker::make('tanggal_expired')
                    ->label('Tanggal Expired')
                    ->required(false)
                    ->nullable()
                    ->live()
                    ->reactive()
                    ->default(null)
                    ->native(false),
                TextInput::make('nomor_dokumen')
                    ->required()
                    ->columnSpan(2),
                FileUpload::make('file')
                    ->label('Upload File')
                    ->disk('public')
                    ->directory('documents')
                    ->columnSpan(2)
                    ->required()
                    ->saveUploadedFileUsing(function ($file, $storage, $get) {
                        $tanggalTerbit  = $get('tanggal_terbit');
                        $namaPerusahaan = $this->getOwnerRecord()->kapal?->perusahaan?->nama_perusahaan;
                        $namaKapal      = $this->getOwnerRecord()->kapal?->nama_kapal;
                        $document       = JenisDokumen::find($this->getOwnerRecord()->id_jenis_dokumen)?->nama_jenis;
                        $tahun          = Carbon::parse($tanggalTerbit)->format('d-M-Y');
                        $time = Carbon::now()->format('H-i-s');

                        $filename = "{$namaPerusahaan}-{$namaKapal}-{$document}-{$tahun}-{$time}." .
                            $file->getClientOriginalExtension();

                        return $file->storeAs(
                            'documents',
                            $filename,
                            'public'
                        );
                    })

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_dokumen')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex()
                    ->color(
                        fn($record) =>
                        $record->id == $record->newQuery()->latest('created_at')->value('id') ? 'info' : null
                    ),
                TextColumn::make('nomor_dokumen')
                    ->color(
                        fn($record) =>
                        $record->id == $record->newQuery()->latest('created_at')->value('id') ? 'info' : null
                    ),
                TextColumn::make('tanggal_terbit')
                    ->color(
                        fn($record) =>
                        $record->id == $record->newQuery()->latest('created_at')->value('id') ? 'info' : null
                    ),
                TextColumn::make('tanggal_expired')
                    ->default('Tidak Ada Tangal Expired')
                    ->color(
                        fn($record) =>
                        $record->id == $record->newQuery()->latest('created_at')->value('id') ? 'info' : null
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->authorize(true)
                    ->label('Renew Dokumen')
                    ->modalHeading('Renew Dokumen')
                    ->modalAlignment(Alignment::Center)
                    ->modalWidth('lg')
                    ->modalIcon('heroicon-o-pencil-square'),
            ])
            ->recordActions([
                Action::make('download')
                    ->size('sm')
                    ->button()
                    ->color('info')
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
                    ->icon('heroicon-o-eye')
                    ->modalWidth('full')
                    ->button()
                    ->color('info')
                    ->modalHeading(fn($record) => $record->nomor_dokumen)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                EditAction::make()
                    ->button()
                    ->authorize(true)
                    ->hidden(
                        fn($record) =>
                        $record->id !== $record->newQuery()->latest('created_at')->value('id')
                    )
                    ->modalHeading('Edit')
                    ->modalAlignment(Alignment::Center)
                    ->modalWidth('lg')
                    ->modalIcon('heroicon-o-pencil-square'),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
