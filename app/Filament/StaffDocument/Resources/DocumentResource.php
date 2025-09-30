<?php

namespace App\Filament\StaffDocument\Resources;

use App\Filament\StaffDocument\Resources\DocumentResource\Pages;
use App\Filament\StaffDocument\Resources\DocumentResource\Pages\EditDocument;
use App\Filament\StaffDocument\Resources\DocumentResource\Pages\HistoryDocument;
use App\Filament\StaffDocument\Resources\DocumentResource\Pages\ViewDocumentExpiration;
use App\Filament\StaffDocument\Resources\DocumentResource\Pages\ViewDocument;

use App\Models\Document;
use App\Models\DocumentExpiration;
use App\Models\JenisKapal;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\JenisDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Carbon;
use Filament\Navigation\NavigationItem;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Document Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Dibuat oleh
                Forms\Components\Select::make('created_by')
                    ->label('Dibuat oleh')
                    ->relationship('createdBy', 'name')
                    ->default(fn() => auth()->id())
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                // =============================
                // Kapal Section
                // =============================
                Forms\Components\Section::make('Kapal')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([

                                // Perusahaan
                                Forms\Components\Select::make('perusahaan_id')
                                    ->label('Perusahaan')
                                    ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => [
                                        $set('jenis_kapal_id', null),
                                        $set('kapal_id', null),
                                    ])
                                    ->dehydrated(false) // tidak disimpan ke documents
                                    ->native(false)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->kapal) {
                                            $component->state($record->kapal->perusahaan_id);
                                        }
                                    }),

                                // Jenis Kapal
                                Forms\Components\Select::make('jenis_kapal_id')
                                    ->label('Jenis Kapal')
                                    ->options(JenisKapal::pluck('nama_jenis', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('kapal_id', null))
                                    ->dehydrated(false) // tidak disimpan ke documents
                                    ->native(false)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->kapal) {
                                            $component->state($record->kapal->jenis_kapal_id);
                                        }
                                    }),

                                // Nama Kapal
                                Forms\Components\Select::make('kapal_id')
                                    ->label('Nama Kapal')
                                    ->options(function (callable $get) {
                                        $perusahaanId = $get('perusahaan_id');
                                        $jenisKapalId = $get('jenis_kapal_id');

                                        if ($perusahaanId && $jenisKapalId) {
                                            return NamaKapal::where('perusahaan_id', $perusahaanId)
                                                ->where('jenis_kapal_id', $jenisKapalId)
                                                ->pluck('nama_kapal', 'id');
                                        }
                                        return [];
                                    })
                                    ->getSearchResultsUsing(
                                        fn(string $search) =>
                                        NamaKapal::where('nama_kapal', 'like', "%{$search}%")
                                            ->pluck('nama_kapal', 'id')
                                    )
                                    ->getOptionLabelUsing(
                                        fn($value): ?string =>
                                        NamaKapal::find($value)?->nama_kapal
                                    )
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),

                // =============================
                // Document Section
                // =============================
                Forms\Components\Section::make('Document')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([

                                // Jenis Dokumen
                                Forms\Components\Select::make('jenis_dokumen_id')
                                    ->relationship('jenisDocument', 'nama_dokumen')
                                    ->native(false)
                                    ->required(),

                                // Nomor Dokumen
                                Forms\Components\TextInput::make('nomor_dokumen')
                                    ->unique(ignorable: fn($record) => $record)
                                    ->required(),

                                // Status
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'UpToDate'   => 'UpToDate',
                                        'Near Expiry' => 'Near Expiry',
                                        'Expired'    => 'Expired',
                                    ])
                                    ->default('UpToDate')
                                    ->disabled()
                                    ->dehydrated()
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),

                // =============================
                // Penerbit Section
                // =============================
                Forms\Components\Section::make('Penerbit')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('penerbit')->required(),
                                Forms\Components\TextInput::make('tempat_penerbitan')->required(),
                                Forms\Components\Textarea::make('keterangan')->columnSpanFull(),
                            ]),
                    ]),

                // =============================
                // Renew Document Section
                // =============================
                Forms\Components\Section::make(request()->boolean('renew') == true ? 'Renew Document' : 'Add Document')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Tanggal Terbit
                                Forms\Components\DatePicker::make('tanggal_terbit')
                                    ->label('Tanggal Terbit')
                                    ->dehydrated(false)
                                    ->live()
                                    ->required(false)
                                    ->nullable()
                                    ->native(false)
                                    ->afterStateHydrated(function ($set, $record) {
                                        if ($record) {
                                            $exp = DocumentExpiration::where('document_id', $record->id)
                                                ->latest('tanggal_terbit')
                                                ->first();

                                            if ($exp) {
                                                $set('tanggal_terbit', $exp->tanggal_terbit);
                                            }
                                        }
                                    }),

                                // Tanggal Expired
                                Forms\Components\DatePicker::make('tanggal_expired')
                                    ->label('Tanggal Expired')
                                    ->dehydrated(false)
                                    ->required(false)
                                    ->nullable()
                                    ->live()
                                    ->default(null)
                                    ->native(false)
                                    ->afterStateHydrated(function ($set, $record) {
                                        if ($record) {
                                            $exp = DocumentExpiration::where('document_id', $record->id)
                                                ->latest('tanggal_expired')
                                                ->first();

                                            if ($exp) {
                                                $set('tanggal_expired', $exp->tanggal_expired);
                                            }
                                        }
                                    })
                            ]),

                        // Upload File
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('documents')
                            ->dehydrated(false)
                            ->required(false)
                            ->nullable()
                            ->columnSpanFull()
                            ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                                $perusahaanId   = $get('perusahaan_id');
                                $kapalId        = $get('kapal_id');
                                $documentId     = $get('jenis_dokumen_id');
                                $tanggalTerbit  = $get('tanggal_terbit');

                                $namaPerusahaan = Perusahaan::find($perusahaanId)?->nama_perusahaan;
                                $namaKapal      = NamaKapal::find($kapalId)?->nama_kapal;
                                $document       = JenisDocument::find($documentId)?->nama_dokumen;
                                $tahun          = Carbon::parse($tanggalTerbit)->format('d-M-Y');

                                return "{$namaPerusahaan}-{$namaKapal}-{$document}-{$tahun}." .
                                    $file->getClientOriginalExtension();
                            }),
                    ]),

                // =============================
                // Reminder Section
                // =============================
                Forms\Components\Repeater::make('reminders')
                    ->label('Reminder Settings')
                    ->relationship('reminders') // relasi hasMany di model DocumentExpiration
                    ->schema([
                        Forms\Components\TextInput::make('reminder_hari')
                            ->label('Hari Reminder')
                            ->numeric()
                            ->prefix('H-')
                            ->required(),
                        Forms\Components\TimePicker::make('reminder_jam')
                            ->label('Jam Reminder')
                            ->required()
                            ->seconds(false)
                            ->time()
                            ->placeholder('Pilih jam'),
                    ])
                    ->hidden(fn($get) => $get('tanggal_expired') == null ? true : false)
                    ->addActionLabel('Buat Reminder')
                    ->addActionAlignment(Alignment::Start)
                    ->collapsible()
                    ->columns(2)
                    ->grid(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('kapal.perusahaan.nama_perusahaan')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisDocument.nama_dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_dokumen')
                    ->icon('heroicon-o-document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status Document')
                    ->color(fn($state) => match ($state) {
                        'UpToDate'    => 'success',
                        'Near Expiry' => 'warning',
                        default       => 'danger',
                    })->sortable(),
            ])
            ->filters([
                SelectFilter::make('perusahaan')
                    ->searchable()
                    ->label('Perusahaan')
                    ->native(false)
                    ->relationship('kapal.perusahaan', 'nama_perusahaan')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_perusahaan)
                    ->preload(),
                SelectFilter::make('status')
                    ->searchable()
                    ->label('Status Document')
                    ->native(false)
                    ->options([
                        'UpToDate' => 'UpToDate',
                        'Near Expiry' => 'Near Expiry',
                        'Expired' => 'Expired'
                    ])
                    ->preload(),
                SelectFilter::make('jenis_document')
                    ->label('Jenis Document')
                    ->searchable()
                    ->native(false)
                    ->relationship('jenisDocument', 'nama_dokumen')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_dokumen)
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Renew')
                        ->color('info')
                        ->url(fn($record) => DocumentResource::getUrl('edit', ['record' => $record, 'renew' => true])),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'exparation' => Pages\ViewDocumentExpiration::route('/{record}/exparation'),
            'activities' => Pages\ViewDocumentHistories::route('/{record}/activities'),

        ];
    }
}
