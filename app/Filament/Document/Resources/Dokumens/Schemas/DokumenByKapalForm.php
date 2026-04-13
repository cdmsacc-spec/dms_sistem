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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class DokumenByKapalForm
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
                                    ->placeholder('')
                                    ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('id_jenis_kapal', null);
                                        $set('id_kapal', null);
                                        $set('dokumen_items', []);
                                    }),

                                Select::make('id_jenis_kapal')
                                    ->label('Jenis Kapal')
                                    ->placeholder('')
                                    ->options(JenisKapal::pluck('nama_jenis', 'id'))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $set('id_kapal', null);
                                        self::populateDokumenItems($set, $state);
                                    })
                                    ->required(),

                                Select::make('id_kapal')
                                    ->label('Nama Kapal')
                                    ->placeholder('')
                                    ->options(function (callable $get) {
                                        $perusahaanId = $get('id_perusahaan');
                                        $jenisKapalId = $get('id_jenis_kapal');
                                        return Kapal::query()
                                            ->when($perusahaanId, fn($q) => $q->where('id_perusahaan', $perusahaanId))
                                            ->when($jenisKapalId, fn($q) => $q->where('id_jenis_kapal', $jenisKapalId))
                                            ->pluck('nama_kapal', 'id')
                                            ->toArray();
                                    })
                                    ->getSearchResultsUsing(function (string $search, callable $get) {
                                        return Kapal::query()
                                            ->when($get('id_perusahaan'), fn($q) => $q->where('id_perusahaan', $get('id_perusahaan')))
                                            ->when($get('id_jenis_kapal'), fn($q) => $q->where('id_jenis_kapal', $get('id_jenis_kapal')))
                                            ->where('nama_kapal', 'ilike', "%{$search}%")
                                            ->pluck('nama_kapal', 'id')
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(fn($value): ?string => Kapal::find($value)?->nama_kapal)
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        if (!$state) return;
                                        $kapal = Kapal::find($state);
                                        if (!$kapal) return;
                                        $set('id_perusahaan', $kapal->id_perusahaan);
                                        if (!$get('id_jenis_kapal')) {
                                            $set('id_jenis_kapal', $kapal->id_jenis_kapal);
                                            self::populateDokumenItems($set, $kapal->id_jenis_kapal);
                                        }
                                    })
                                    ->required(),
                            ]),
                    ]),

                Section::make('Dokumen')
                    ->columnSpanFull()
                    ->schema([

                        Repeater::make('dokumen_items')
                            ->label('')
                            ->itemLabel(function (array $state, callable $get) {
                                $items = $get('dokumen_items') ?? [];
                                foreach ($items as $i => $item) {
                                    if (($item['id_jenis_dokumen'] ?? null) === ($state['id_jenis_dokumen'] ?? null)) {
                                        return 'Dokumen ke-' . ((int) $i + 1);
                                    }
                                }
                                return 'Dokumen';
                            })
                            ->schema([

                                Hidden::make('id_jenis_dokumen'),

                                // ── Info Dokumen ───────────────────────────────────────
                                TextInput::make('nama_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(['sm' => 2, 'lg' => 2]),

                                TextInput::make('penerbit')
                                    ->label('Penerbit')
                                    ->datalist(['HUBLA', 'KSOP', 'BKI'])
                                    ->columnSpan(['sm' => 2, 'lg' => 1]),

                                // TextInput::make('tempat_penerbitan')
                                //     ->label('Tempat Penerbitan')
                                //     ->datalist(['Jakarta'])
                                //     ->columnSpan(['sm' => 2, 'lg' => 1]),

                                // Textarea::make('keterangan')
                                //     ->label('Keterangan')
                                //     ->rows(2)
                                //     ->columnSpan(['sm' => 2, 'lg' => 2]),
                                
                                TextInput::make('nomor_dokumen')
                                    ->label('Nomor Dokumen')
                                    ->columnSpan(['sm' => 2, 'lg' => 1]),

                                DatePicker::make('tanggal_terbit')
                                    ->label('Tanggal Terbit')
                                    ->displayFormat('d-M-Y')
                                    ->native(false)
                                    ->columnSpan(['sm' => 2, 'lg' => 1]),

                                DatePicker::make('tanggal_expired')
                                    ->label('Tanggal Expired')
                                    ->displayFormat('d-M-Y')
                                    ->native(false)
                                    ->nullable()
                                    ->live()
                                    ->required()
                                    ->columnSpan(['sm' => 2, 'lg' => 1]),

                                FileUpload::make('file')
                                    ->label('Upload File')
                                    ->disk('public')
                                    ->directory('documents')
                                    ->required()
                                    ->columnSpan(['sm' => 2, 'lg' => 1])
                                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get, $livewire) {
                                        $kapalId        = $livewire->data['id_kapal'] ?? null;
                                        $jenisDokumenId = $get('id_jenis_dokumen');
                                        $tanggalTerbit  = $get('tanggal_terbit');

                                        $kapal          = Kapal::find($kapalId);
                                        $namaPerusahaan = $kapal?->perusahaan?->nama_perusahaan ?? 'Unknown';
                                        $namaKapal      = $kapal?->nama_kapal ?? 'Unknown';
                                        $document       = JenisDokumen::find($jenisDokumenId)?->nama_jenis ?? 'Dokumen';
                                        $tanggal        = $tanggalTerbit
                                            ? Carbon::parse($tanggalTerbit)->format('d-M-Y')
                                            : now()->format('d-M-Y');
                                        $time           = Carbon::now()->format('H-i-s');

                                        return "{$namaPerusahaan}-{$namaKapal}-{$document}-{$tanggal}-{$time}."
                                            . $file->getClientOriginalExtension();
                                    }),

                                // ── Reminder (hanya jika tanggal_expired diisi) ────────
                                
                            ])
                            ->columns(['sm' => 2, 'lg' => 4])
                            ->grid(1)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                    Section::make('Reminder')
                        ->columnSpanFull()
                        ->collapsible()
                        ->collapsed(false)
                        ->visible(fn ($get) =>
                            collect($get('dokumen_items'))
                                ->pluck('tanggal_expired')
                                ->filter()
                                ->count() === collect($get('dokumen_items'))->count()
                        )
                        // ->visible(fn(callable $get) => filled($get('tanggal_expired')))
                        ->schema([

                            // ── Dropdown template + tombol delete sejajar ──
                            Select::make('reminder_template_id')
                                ->label('Template Reminder')
                                ->placeholder('Pilih template (opsional)')
                                ->native(false)
                                ->live()
                                ->options(fn() => ReminderTemplate::pluck('nama_template', 'id')->toArray())
                                ->afterStateUpdated(function (callable $set, $state) {
                                    if (!$state) return;
                                    $template = ReminderTemplate::with('reminderItems', 'toReminderItems')->find($state);
                                    if (!$template) return;

                                    $set('reminder_dokumen', $template->reminderItems
                                        ->map(fn($item) => [
                                            'reminder_hari' => $item->reminder_hari,
                                            'reminder_jam'  => $item->reminder_jam,
                                        ])
                                        ->values()->toArray()
                                    );

                                    $set('to_reminder_dokumen', $template->toReminderItems
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

                            // ── Jadwal Reminder ───────────────────
                            Repeater::make('reminder_dokumen')
                                ->label('Jadwal Reminder')
                                ->addActionLabel('Tambah Jadwal')
                                ->addActionAlignment(Alignment::Start)
                                ->table([
                                    TableColumn::make('reminder_hari')->markAsRequired(),
                                    TableColumn::make('reminder_jam')->markAsRequired(),
                                ])
                                ->schema([
                                    TextInput::make('reminder_hari')
                                        ->label('Hari')
                                        ->numeric()
                                        ->prefix('H-')
                                        ->required(),
                                    TimePicker::make('reminder_jam')
                                        ->label('Jam')
                                        ->required()
                                        ->seconds(false)
                                        ->placeholder('Pilih jam'),
                                ]),

                            // ── Penerima Reminder ─────────────────
                            Repeater::make('to_reminder_dokumen')
                                ->label('Penerima Reminder')
                                ->addActionLabel('Tambah Penerima')
                                ->addActionAlignment(Alignment::Start)
                                ->table([
                                    TableColumn::make('nama')->markAsRequired(),
                                    TableColumn::make('send_to')->markAsRequired(),
                                    TableColumn::make('type')->markAsRequired()->width('150px'),
                                ])
                                ->schema([
                                    TextInput::make('nama')->label('Nama')->required(),
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
                                    Select::make('type')
                                        ->native(false)
                                        ->placeholder('')
                                        ->required()
                                        ->options(['wa' => 'WA', 'email' => 'Email']),
                                ]),
                            
                            // ── Save as template ───────────────────────────
                            Toggle::make('save_as_template')
                                ->label('Simpan pengaturan reminder ini sebagai template')
                                ->live()
                                ->default(false)
                                ->dehydrated(fn ($state) => $state === true)
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

    private static function populateDokumenItems(callable $set, ?int $jenisKapalId): void
    {
        if (!$jenisKapalId) {
            $set('dokumen_items', []);
            return;
        }

        $items = JenisDokumen::whereHas('jenisKapal', function ($q) use ($jenisKapalId) {
            $q->where('jenis_kapal_dokumen.id_jenis_kapal', $jenisKapalId);
        })
            ->get()
            ->map(fn($doc) => [
                'id_jenis_dokumen'     => $doc->id,
                'nama_dokumen'         => $doc->nama_jenis,
                'penerbit'             => null,
                'tempat_penerbitan'    => null,
                'keterangan'           => null,
                'tanggal_terbit'       => null,
                'tanggal_expired'      => null,
                'nomor_dokumen'        => null,
                'file'                 => null,
                'reminder_template_id' => null,
                'reminder_dokumen'     => [],
                'to_reminder_dokumen'  => [],
                'save_as_template'     => false,
                'template_name'        => null,
            ])
            ->values()
            ->toArray();

        $set('dokumen_items', $items);
    }
}