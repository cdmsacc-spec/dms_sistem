<?php

namespace App\Filament\Document\Resources\Dokumens\Schemas;

use App\Models\JenisDokumen;
use App\Models\JenisKapal;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\ReminderTemplate;
use Carbon\Carbon;
use Filament\Actions\Action;
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
                                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                        if ($get('id_kapal')) return;
                                        $set('id_jenis_kapal', null);
                                        $set('id_kapal', null);
                                    })
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
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        if ($get('id_kapal')) return;
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
                                    ->preload()
                                    ->placeholder('')
                                    ->options(function (callable $get) {
                                        $perusahaanId = $get('id_perusahaan');
                                        $jenisKapalId = $get('id_jenis_kapal');
                                        return Kapal::query()
                                            ->when($perusahaanId, fn($q) => $q->where('id_perusahaan', $perusahaanId))
                                            ->when($jenisKapalId, fn($q) => $q->where('id_jenis_kapal', $jenisKapalId))
                                            ->pluck('nama_kapal', 'id');
                                    })
                                    ->getSearchResultsUsing(function (string $search, callable $get) {
                                        return Kapal::query()
                                            ->when($get('id_perusahaan'), fn($q) => $q->where('id_perusahaan', $get('id_perusahaan')))
                                            ->when($get('id_jenis_kapal'), fn($q) => $q->where('id_jenis_kapal', $get('id_jenis_kapal')))
                                            ->where('nama_kapal', 'ilike', "%{$search}%")
                                            ->pluck('nama_kapal', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if (!$state) return;
                                        $kapal = Kapal::find($state);
                                        if ($kapal) {
                                            $set('id_perusahaan', $kapal->id_perusahaan);
                                            $set('id_jenis_kapal', $kapal->id_jenis_kapal);
                                        }
                                    })
                                    ->getOptionLabelUsing(fn($value): ?string => Kapal::find($value)?->nama_kapal)
                                    ->reactive()
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
                                    ->label('Jenis Dokumen')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('')
                                    ->options(function (callable $get) {
                                        $jenisKapalId = $get('id_jenis_kapal');
                                        if (!$jenisKapalId) {
                                            return JenisDokumen::pluck('nama_jenis', 'id');
                                        }
                                        $query = JenisDokumen::whereHas('jenisKapal', function ($q) use ($jenisKapalId) {
                                            $q->where('id_jenis_kapal', $jenisKapalId);
                                        });
                                        if ($query->exists()) {
                                            return $query->pluck('nama_jenis', 'id');
                                        }
                                        return JenisDokumen::pluck('nama_jenis', 'id');
                                    })
                                    ->getOptionLabelUsing(fn($value): ?string => JenisDokumen::find($value)?->nama_jenis)
                                    ->reactive()
                                    ->native(false)
                                    ->required(),
                                TextInput::make('penerbit')->datalist(['HUBLA', 'KSOP', 'BKI']),
                                // TextInput::make('tempat_penerbitan')->columnSpanFull()->required(),
                                // Textarea::make('keterangan')->columnSpanFull()->rows(4)->required(),
                            ]),
                    ]),

                Repeater::make('historyDokumen')
                    ->hiddenLabel()
                    ->relationship('historyDokumen')
                    ->schema([
                        DatePicker::make('tanggal_terbit')
                            ->label('Tanggal Terbit')
                            ->live()
                            ->displayFormat('d-M-Y')
                            ->native(false),
                        DatePicker::make('tanggal_expired')
                            ->label('Tanggal Expired')
                            ->required(false)
                            ->nullable()
                            ->displayFormat('d-M-Y')
                            ->live()
                            ->required()
                            ->reactive()
                            ->default(null)
                            ->native(false),
                        TextArea::make('nomor_dokumen')
                            ->unique(ignoreRecord: true)
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
                
                Section::make('Reminder')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(false)
                    ->visible(function (callable $get, $livewire) {
                        if (!($livewire instanceof \Filament\Resources\Pages\CreateRecord)) {
                            return false;
                        }
                        $fileDocs = $get('historyDokumen');
                        if (!is_array($fileDocs)) {
                            return false;
                        }
                        return collect($fileDocs)->pluck('tanggal_expired')->filter()->isNotEmpty();
                    })
                    ->schema([
                        // ── Dropdown template dengan suffixAction tombol delete ─────────
                        Select::make('reminder_template_id')
                            ->label('Template Reminder')
                            ->placeholder('Pilih template (opsional)')
                            ->native(false)
                            ->live()
                            ->columnSpanFull()
                            ->options(fn() => ReminderTemplate::pluck('nama_template', 'id')->toArray())
                            ->afterStateUpdated(function (callable $set, $state) {
                                if (!$state) return;
                                $template = ReminderTemplate::with('reminderItems', 'toReminderItems')->find($state);
                                if (!$template) return;

                                $set('reminderDokumen', $template->reminderItems
                                    ->map(fn($item) => [
                                        'reminder_hari' => $item->reminder_hari,
                                        'reminder_jam'  => $item->reminder_jam,
                                    ])
                                    ->values()->toArray()
                                );

                                $set('toReminderDokumen', $template->toReminderItems
                                    ->map(fn($item) => [
                                        'nama'    => $item->nama,
                                        'send_to' => $item->send_to,
                                        'type'    => $item->type,
                                    ])
                                    ->values()->toArray()
                                );
                            })
                            ->dehydrated(false)
                            ->suffixAction(
                                Action::make('delete_template')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->tooltip('Hapus template ini')
                                    ->visible(fn(callable $get) => filled($get('reminder_template_id')))
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Template Reminder')
                                    ->modalDescription(fn(callable $get) => 'Apakah Anda yakin ingin menghapus template "' .
                                        (ReminderTemplate::find($get('reminder_template_id'))?->nama_template ?? '') .
                                        '"? Tindakan ini tidak dapat dibatalkan.')
                                    ->modalSubmitActionLabel('Ya, Hapus')
                                    ->modalCancelActionLabel('Batal')
                                    ->modalIcon('heroicon-o-exclamation-triangle')
                                    ->action(function (callable $get, callable $set) {
                                        $templateId = $get('reminder_template_id');
                                        if (!$templateId) return;
                                        ReminderTemplate::find($templateId)?->delete();
                                        $set('reminder_template_id', null);
                                    })
                            ),

                        Repeater::make('reminderDokumen')
                            ->relationship('reminderDokumen')
                            ->addActionLabel('Add Reminder')
                            ->columnSpan(2)
                            ->addActionAlignment(Alignment::Start)
                            // ->visible(function ($get,   $livewire) {
                            //     $fileDocs = $get('historyDokumen');
                            //     if (!is_array($fileDocs)) {
                            //         return false;
                            //     }
                            //     $expiredDates = collect($fileDocs)
                            //         ->pluck('tanggal_expired')
                            //         ->filter()
                            //         ->values()
                            //         ->toArray();
                            //     return   $expiredDates == null ? false : true;
                            // })
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
                            // ->visible(function ($get) {
                            //     $fileDocs = $get('historyDokumen');
                            //     if (!is_array($fileDocs)) {
                            //         return false;
                            //     }

                            //     $expiredDates = collect($fileDocs)
                            //         ->pluck('tanggal_expired')
                            //         ->filter()
                            //         ->values()
                            //         ->toArray();
                            //     return   $expiredDates == null ? false : true;
                            // })
                            ->table([
                                TableColumn::make('nama')->markAsRequired(),
                                TableColumn::make('type')->markAsRequired()
                                    ->width('200px'),
                                TableColumn::make('send_to')->markAsRequired(),

                            ])
                            ->schema([
                                TextInput::make('nama')->label('Nama')->required(),
                                Select::make('type')
                                    ->native(false)
                                    ->placeholder('')
                                    ->required()
                                    ->live()
                                    ->options(['wa' => 'WA', 'email' => 'Email']),
                                TextInput::make('send_to')
                                    ->label('Send To')
                                    ->dehydrateStateUsing(function ($state, $get) {
                                        if ($get('type') === 'wa' && filled($state)) {
                                            $cleanState = preg_replace('/[^0-9]/', '', $state);

                                            return str_starts_with($cleanState, '0')
                                                ? '62' . substr($cleanState, 1)
                                                : $cleanState;
                                        }

                                        return $state;
                                    })
                                    ->required(),

                            ]),

                        // ── Save as template ───────────────────────────────────────────
                        Toggle::make('save_as_template')
                            ->label('Simpan pengaturan reminder ini sebagai template')
                            ->live()
                            ->default(false)
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        TextInput::make('template_name')
                            ->label('Nama Template')
                            ->placeholder('Contoh: Template Reminder BKI 30 Hari')
                            ->required()
                            ->visible(fn(callable $get) => (bool) $get('save_as_template'))
                            ->dehydrated(fn(callable $get) => (bool) $get('save_as_template'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
