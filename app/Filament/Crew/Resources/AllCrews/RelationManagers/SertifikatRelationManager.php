<?php

namespace App\Filament\Crew\Resources\AllCrews\RelationManagers;

use App\Models\Lookup;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SertifikatRelationManager extends RelationManager
{
    protected static string $relationship = 'sertifikat';
    public static function getBadge($ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->sertifikat->count();
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kategory')
                    ->columns(1)
                    ->placeholder('')
                    ->native(false)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(Lookup::where('type', 'dokumen_crew')
                        ->where('code',  'kategory_certificate_crew')
                        ->pluck('name', 'name')
                        ->toArray()),
                TextInput::make('nomor_sertifikat')
                    ->required()
                    ->columns(1),
                TextInput::make('nama_sertifikat')
                    ->required()
                    ->columns(1),
                TextInput::make('tempat_dikeluarkan')
                    ->required()
                    ->columns(1),
                DatePicker::make('tanggal_terbit')
                    ->displayFormat('d-M-Y')
                    ->prefixIcon('heroicon-m-calendar')
                    ->required()
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
                    ->directory('crew/sertifikat')
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
                            $jenis     = $get('nama_sertifikat') ?? 'sertifikat';
                            $now       = now()->format('YmdHis');
                            $filename = strtolower(
                                preg_replace('/[^A-Za-z0-9\-]/', '_', "{$nama_crew}-{$jenis}-{$now}")
                            ) . '.' . $file->getClientOriginalExtension();

                            return $filename;
                        } catch (\Throwable $e) {
                            \Log::error("Error generate filename: " . $e->getMessage());
                            throw $e;
                        }
                    }),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_sertifikat'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_sertifikat')
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->heading('')
            ->defaultPaginationPageOption('5')
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->width('sm')
                    ->rowIndex(),
                TextColumn::make('nomor_sertifikat')
                    ->searchable(),
                TextColumn::make('nama_sertifikat')
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
                    ->label('Add Sertifikat')
                    ->modalHeading('Add Data Sertifikat')
                    ->mutateDataUsing(function (array $data): array {
                        $data['status'] = 'uptodate';
                        return $data;
                    }),
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
                    ->modalHeading(fn($record) => $record->nama_sertifikat)
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
