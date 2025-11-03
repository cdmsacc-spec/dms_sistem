<?php

namespace App\Filament\Document\Resources;

use App\Filament\Document\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\DocumentExpiration;
use App\Models\JenisKapal;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\JenisDocument;
use Filament\Forms;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Carbon;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

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
                                    })
                                    ->required(),

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
                                    })
                                    ->required(),

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
                        Forms\Components\Grid::make(2)
                            ->schema([

                                // Jenis Dokumen
                                Forms\Components\Select::make('jenis_dokumen_id')
                                    ->searchable()
                                    ->preload()
                                    ->relationship('jenisDocument', 'nama_dokumen')
                                    ->native(false)
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
                        Forms\Components\Grid::make(3)
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
                                                ->latest()
                                                ->first();

                                            if ($exp) {
                                                $set('tanggal_expired', $exp->tanggal_expired);
                                            }
                                        }
                                    }),
                                // Nomor Dokumen
                                Forms\Components\TextInput::make('nomor_dokumen')
                                    ->dehydrated(false),
                            ]),

                        // Upload File
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('documents')
                            ->dehydrated(false)
                            ->nullable()
                            ->columnSpanFull()
                            ->getUploadedFileNameForStorageUsing(function ($file, callable $get,) {
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

                Forms\Components\Repeater::make('reminderemail')
                    ->label('Reminder Email Settings')
                    ->relationship('reminderemail') // relasi hasMany di model DocumentExpiration
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ])
                    ->hidden(fn($get) => $get('tanggal_expired') == null ? true : false)
                    ->addActionLabel('Add Email')
                    ->addActionAlignment(Alignment::Start)
                    ->collapsible()
                    ->columns(1)
                    ->grid(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->leftJoin(\DB::raw(
                'LATERAL (
                SELECT de.tanggal_expired
                FROM document_expirations de
                WHERE de.document_id = documents.id
                ORDER BY de.id DESC
                LIMIT 1
            ) AS latest_de'
            ), \DB::raw('true'), '=', \DB::raw('true'))
            ->addSelect('documents.*')
            ->addSelect(\DB::raw('(latest_de.tanggal_expired - CURRENT_DATE) AS jarak_hari'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('kapal.perusahaan.nama_perusahaan')
            ->groups([
                Group::make('kapal.perusahaan.nama_perusahaan')
                    ->label('Perusahaan')
                    ->collapsible(),
                Group::make('kapal.nama_kapal')
                    ->label('Kapal')
                    ->collapsible(),
                Group::make('jenisDocument.nama_dokumen')
                    ->label('Jenis')
                    ->collapsible(),
            ])
            ->groupingDirectionSettingHidden()
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('kapal.perusahaan.nama_perusahaan')
                    ->searchable()
                    ->formatStateUsing(fn($state) => "<strong>{$state}</strong>")
                    ->html(),
                Tables\Columns\TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisDocument.nama_dokumen')
                    ->label('Jenis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latestExpiration.nomor_dokumen')
                    ->label('Nomor Dokumen'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'UpToDate'    => 'success',
                        'Near Expiry' => 'warning',
                        default       => 'danger',
                    })->sortable(),
                Tables\Columns\TextColumn::make('jarak_hari')
                    ->label('Jarak hari expired')
                    ->sortable()
                    ->getStateUsing(
                        fn($record) => $record->jarak_hari !== null
                            ? $record->jarak_hari . ' hari'
                            : 'Tanpa Expired'
                    ),
                Tables\Columns\TextColumn::make('last_comment')->html()
            ])
            ->filters([
                Filter::make('kapal_perusahaan')
                    ->columnSpan(2)
                    ->columns(2)

                    ->form([
                        Select::make('perusahaan')
                            ->label('Perusahaan')
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id')->toArray())
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(1)
                            ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set, $state) {

                                if (empty($state)) {
                                    $set('kapal', null);
                                }
                            }),
                        Select::make('kapal')
                            ->label('Kapal')
                            ->columnSpan(1)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(function (callable $get) {
                                $perusahaanId = $get('perusahaan');
                                if ($perusahaanId) {
                                    return Namakapal::where('perusahaan_id', $perusahaanId)
                                        ->pluck('nama_kapal', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['perusahaan'])) {
                            $query->whereHas('kapal.perusahaan', fn($q) => $q->where('id', $data['perusahaan']));
                        }
                        if (!empty($data['kapal'])) {
                            $query->where('kapal_id', $data['kapal']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $texts = [];

                        if (!empty($data['perusahaan'])) {
                            $namaPerusahaan = Perusahaan::find($data['perusahaan'])->nama_perusahaan ?? '—';
                            $texts[] = "Perusahaan: {$namaPerusahaan}";
                        }

                        if (!empty($data['kapal'])) {
                            $namaKapal = Namakapal::find($data['kapal'])->nama_kapal ?? '—';
                            $texts[] = "Kapal: {$namaKapal}";
                        }

                        return $texts ? implode(', ', $texts) : null;
                    }),

                SelectFilter::make('jenis_document')
                    ->label('Jenis Document')
                    ->searchable()
                    ->native(false)
                    ->relationship('jenisDocument', 'nama_dokumen')
                    ->getOptionLabelFromRecordUsing(fn($record): string =>  $record->nama_dokumen)
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
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\Action::make('download')
                    ->size('sm')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->latestExpiration->file_path), shouldOpenInNewTab: true)
                    ->visible(function ($record) {
                        $path = $record->latestExpiration->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension != 'pdf';
                    }),

                MediaAction::make('priview')
                    ->label('Priview‎ ‎ ')
                    ->size('sm')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => $record->kapal->nama_kapal)
                    ->media(fn($record) => str_replace(' ', '%20', Storage::url($record->latestExpiration->file_path)))
                    ->visible(function ($record) {
                        $path = $record->latestExpiration->file_path ?? null;
                        if (! $path) return false;
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        return $extension === 'pdf';
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Renew')
                        ->url(fn($record) => DocumentResource::getUrl('edit', ['record' => $record, 'renew' => true])),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                       ,
                    Tables\Actions\BulkAction::make('Download File')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->openUrlInNewTab()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $currentDate = now()->format('Y-m-d_H-i-s');
                            $zipFileName = "{$currentDate}". "_documents.zip";

                            return new StreamedResponse(function () use ($records) {
                                $zip = new ZipStream();

                                foreach ($records as $record) {
                                    $filePath = optional($record->latestExpiration)->file_path;
                                    if (! $filePath) continue;

                                    $disk = Storage::disk('public');
                                    $filePath = ltrim($filePath, '/');

                                    if ($disk->exists($filePath)) {
                                        $absolutePath = $disk->path($filePath);
                                        $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $record->name ?? basename($filePath));
                                        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                                        $fileNameInZip = "{$safeName}.{$ext}";

                                        $zip->addFileFromPath($fileNameInZip, $absolutePath);
                                    }
                                }

                                $zip->finish();
                            }, 200, [
                                'Content-Type' => 'application/zip',
                                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                            ]);
                        }),
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
