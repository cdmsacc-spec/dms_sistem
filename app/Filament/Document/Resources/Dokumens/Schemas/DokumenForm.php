<?php

namespace App\Filament\Document\Resources\Dokumens\Schemas;

use App\Models\JenisDokumen;
use App\Models\JenisKapal;
use App\Models\Kapal;
use App\Models\Perusahaan;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class DokumenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kapal')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('id_perusahaan')
                                    ->label('Perusahaan')
                                    ->searchable()
                                    ->placeholder('')
                                    ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->native(false)
                                    ->afterStateUpdated(fn(callable $set, $state) => [
                                        $set('id_jenis_kapal', null),
                                        $set('id_kapal', null),
                                    ])
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->kapal) {
                                            $component->state($record->kapal->id_perusahaan);
                                        }
                                    })
                                    ->required(),

                                Select::make('id_jenis_kapal')
                                    ->label('Jenis Kapal')
                                    ->searchable()
                                    ->placeholder('')
                                    ->options(JenisKapal::pluck('nama_jenis', 'id'))
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->native(false)
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('id_kapal', null);
                                    })
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && $record->kapal) {
                                            $component->state($record->kapal->id_jenis_kapal);
                                        }
                                    })
                                    ->required(),

                                Select::make('id_kapal')
                                    ->label('Nama Kapal')
                                    ->placeholder('')
                                    ->options(function (callable $get) {
                                        $perusahaanId = $get('id_perusahaan');
                                        $jenisKapalId = $get('id_jenis_kapal');
                                        if ($perusahaanId && $jenisKapalId) {
                                            return Kapal::where('id_perusahaan', $perusahaanId)
                                                ->where('id_jenis_kapal', $jenisKapalId)
                                                ->pluck('nama_kapal', 'id');
                                        }
                                        return [];
                                    })
                                    ->getSearchResultsUsing(
                                        fn(string $search) =>
                                        Kapal::where('nama_kapal', 'like', "%{$search}%")
                                            ->pluck('nama_kapal', 'id')
                                    )
                                    ->getOptionLabelUsing(
                                        fn($value): ?string =>
                                        Kapal::find($value)?->nama_kapal
                                    )
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Dokumen')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('id_jenis_dokumen')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('')
                                    ->relationship('jenisDokumen', 'nama_jenis')
                                    ->native(false)
                                    ->required(),
                                TextInput::make('penerbit')->required(),
                                TextInput::make('tempat_penerbitan')->columnSpanFull()->required(),
                                Textarea::make('keterangan')->columnSpanFull()->rows(4)->required(),
                            ]),
                    ]),

                Repeater::make('historyDokumen')
                    ->hiddenLabel()
                    ->relationship('historyDokumen')
                    ->schema([
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
                        TextArea::make('nomor_dokumen')
                            ->required()
                            ->rows(3)
                            ->columnSpan(1),
                        FileUpload::make('file')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('documents')
                            ->columnSpan(1)
                            ->required()
                            ->panelAspectRatio(1 / 7)
                            ->getUploadedFileNameForStorageUsing(function ($file, callable $get, $livewire) {
                                $rootState = $livewire->data ?? [];
                                $perusahaanId   = $rootState['id_perusahaan'];
                                $kapalId        = $rootState['id_kapal'];
                                $documentId     = $rootState['id_jenis_dokumen'];
                                $tanggalTerbit  = $get('tanggal_terbit');

                                $namaPerusahaan = Perusahaan::find($perusahaanId)?->nama_perusahaan;
                                $namaKapal      = Kapal::find($kapalId)?->nama_kapal;
                                $document       = JenisDokumen::find($documentId)?->nama_jenis;
                                $tahun          = Carbon::parse($tanggalTerbit)->format('d-M-Y');
                                return "{$namaPerusahaan}-{$namaKapal}-{$document}-{$tahun}." .
                                    $file->getClientOriginalExtension();
                            }),
                    ])
                    ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->columns(2)
                    ->grid(1)
                    ->addable(false)
                    ->deletable(false)
                    ->columnSpan(2),

                Repeater::make('reminderDokumen')
                    ->relationship('reminderDokumen')
                    ->addActionLabel('Add Reminder')
                    ->columnSpan(2)
                    ->addActionAlignment(Alignment::Start)
                    ->visible(function ($get,   $livewire) {
                        $fileDocs = $get('historyDokumen');
                        if (!is_array($fileDocs)) {
                            return false;
                        }
                        $expiredDates = collect($fileDocs)
                            ->pluck('tanggal_expired')
                            ->filter()
                            ->values()
                            ->toArray();
                        return   $expiredDates == null ? false : true;
                    })
                    ->table([
                        TableColumn::make('reminder_hari')->markAsRequired(),
                        TableColumn::make('reminder_jam')->markAsRequired(),
                    ])
                    ->schema([
                        TextInput::make('reminder_hari')
                            ->label('Hari')
                            ->numeric()
                            ->columnSpan(2)
                            ->prefix('H-')
                            ->required(),
                        TimePicker::make('reminder_jam')
                            ->label('Jam')
                            ->required()
                            ->seconds(false)
                            ->time()
                            ->columnSpan(1)
                            ->placeholder('Pilih jam'),
                    ]),

                Repeater::make('toReminderDokumen')
                    ->relationship('toReminderDokumen')
                    ->addActionLabel('Add Reminder')
                    ->columnSpan(2)
                    ->addActionAlignment(Alignment::Start)
                    ->visible(function ($get) {
                        $fileDocs = $get('historyDokumen');
                        if (!is_array($fileDocs)) {
                            return false;
                        }

                        $expiredDates = collect($fileDocs)
                            ->pluck('tanggal_expired')
                            ->filter()
                            ->values()
                            ->toArray();
                        return   $expiredDates == null ? false : true;
                    })
                    ->table([
                        TableColumn::make('nama')->markAsRequired(),
                        TableColumn::make('send_to')->markAsRequired(),
                        TableColumn::make('type')->markAsRequired()
                            ->width('150px'),
                    ])
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('send_to')
                            ->label('Send To')
                            ->required(),
                        Select::make('type')
                            ->native(false)
                            ->placeholder('')
                            ->required()
                            ->options([
                                'wa' => "Wa",
                                'email' => "Email",
                            ])
                    ])
            ]);
    }
}
