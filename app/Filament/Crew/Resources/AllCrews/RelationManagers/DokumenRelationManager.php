<?php

namespace App\Filament\Crew\Resources\AllCrews\RelationManagers;

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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

class DokumenRelationManager extends RelationManager
{
    protected static string $relationship = 'dokumen';
    public static function getBadge($ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->dokumen->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kategory')
                    ->columns(1)
                    ->native(false)
                    ->columnSpan(1)
                    ->placeholder('')
                    ->required()
                    ->options([
                        'Document Pelaut' => 'Document Pelaut',
                        'Sertifikat Keahlian / Pengukuhan' => 'Sertifikat Keahlian / Pengukuhan',
                    ]),
                TextInput::make('nomor_dokumen')
                    ->required()
                    ->columns(1),
                Select::make('jenis_dokumen')
                    ->columns(1)
                    ->placeholder('')
                    ->native(false)
                    ->required()
                    ->options([
                        'Passport' => 'Passport',
                        'Seaman Book' => 'Seaman Book',
                        'MCU' => 'MCU',
                        'COC' => 'COC',
                        'COE' => 'COE',
                        'GMDSS' => 'GMDSS'
                    ]),
                TextInput::make('tempat_dikeluarkan')
                    ->required()
                    ->columns(1),
                DatePicker::make('tanggal_terbit')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
                    ->displayFormat('d-M-Y')
                    ->native(false)
                    ->columns(1),
                DatePicker::make('tanggal_expired')
                    ->displayFormat('d-M-Y')
                    ->prefixIcon('heroicon-m-calendar')
                    ->native(false)
                    ->columns(1),
                FileUpload::make('file')
                    ->label('File')
                    ->disk('public')
                    ->directory('crew/dokumen')
                    ->columnSpan(3)
                    ->required()
                    ->downloadable()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                        try {
                            $nama_crew = optional($this->ownerRecord)->nama_crew ?? 'crew';
                            $jenis     = $get('jenis_document') ?? 'dokumen';
                            $now       = now()->format('YmdHis');

                            $filename = strtolower(
                                preg_replace('/[^A-Za-z0-9\-]/', '_', "{$nama_crew}-{$jenis}-{$now}")
                            ) . '.' . $file->getClientOriginalExtension();

                            \Log::info("FileUpload generate name: {$filename}");

                            return $filename;
                        } catch (\Throwable $e) {
                            \Log::error("FileUpload error: " . $e->getMessage());
                            throw $e; // biar error kelihatan
                        }
                    }),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('jenis_dokumen'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_dokumen')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nomor_dokumen')
                    ->label('Nomor')
                    ->searchable(),
                TextColumn::make('jenis_dokumen')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('kategory')
                    ->searchable(),
                TextColumn::make('tempat_dikeluarkan'),
                TextColumn::make('tanggal_terbit')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-M-Y')),
                TextColumn::make('tanggal_expired')
                    ->formatStateUsing(fn($record) => $record->tanggal_expired ? Carbon::parse($record->tanggal_expired)->format('d-M-Y') : 'Tidak Ada Tangal Expired'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'uptodate' => 'success',
                        'near expiry' => 'warning',
                        'expired' => 'danger',
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Dokumen')
                    ->modalHeading('Add Crew Document')
                    ->mutateDataUsing(function (array $data): array {
                        $data['status'] = 'uptodate';
                        return $data;
                    })
            ])
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
                    ->modalHeading(fn($record) => $record->jenis_dokumen)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->file)))
                    ->visible(function ($record) {
                        $path = $record->file ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),
                EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
