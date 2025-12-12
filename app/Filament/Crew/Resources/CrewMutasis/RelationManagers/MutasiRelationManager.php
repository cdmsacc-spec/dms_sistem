<?php

namespace App\Filament\Crew\Resources\CrewMutasis\RelationManagers;

use App\Models\Jabatan;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use App\Models\CrewKontrak;

class MutasiRelationManager extends RelationManager
{
    protected static string $relationship = 'kontrak';
    protected $listeners = ['refresh' => '$refresh'];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penempatan Crew')
                    ->description('Informasi perusahaan, wilayah, kapal, dan jabatan crew')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 3
                    ])
                    ->columnSpanFull()
                    ->schema([
                        Select::make('id_perusahaan')
                            ->label('Pilih Perusahaan')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->native(false)
                            ->reactive()
                            ->columnSpan(2),

                        Select::make('id_wilayah')
                            ->label('Wilayah Operasional')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options(WilayahOperasional::pluck('nama_wilayah', 'id'))
                            ->native(false),
                        Select::make('id_kapal')
                            ->label('Nama Kapal')
                            ->placeholder('')
                            ->preload()
                            ->options(function (callable $get) {
                                $perusahaanId = $get('id_perusahaan');
                                if ($perusahaanId) {
                                    return Kapal::where('id_perusahaan', $perusahaanId)
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
                            ->native(false),

                        Select::make('id_jabatan')
                            ->label('Jabatan Crew')
                            ->options(Jabatan::all()
                                ->mapWithKeys(fn($jabatan) => [
                                    $jabatan->id => "{$jabatan->nama_jabatan} ({$jabatan->devisi} - {$jabatan->golongan})",
                                ]))
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('berangkat_dari')
                            ->label('Berangkat Dari')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options([
                                'Jakarta' => 'Jakarta',
                                'Lokal' => 'Lokal'
                            ])
                            ->native(false),

                    ]),

                Section::make('Kontrak Crew')
                    ->description('Detail kontrak, gaji, tanggal mulai dan selesai, serta status kontrak')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 3
                    ])
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('gaji')
                            ->label('Gaji (Rp.)')
                            ->prefix('Rp.')
                            ->mask(RawJs::make('$money($input)'))
                            ->dehydrated(true),

                        Select::make('kontrak_lanjutan')
                            ->label('Kontrak')
                            ->native(false)
                            ->options([
                                false => 'Baru',
                                true => 'Lanjutan'
                            ])
                            ->reactive()
                            ->placeholder('')
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($record, $state, callable $set, callable $get) {
                                $oldContract = $record->lastKontrak()
                                    ->latest('end_date')
                                    ->first();
                                if ($state == 1 && $oldContract) {
                                    $set('end_date', $oldContract->end_date);
                                } else if ($state == 0) {
                                    $set('end_date', null);
                                }
                            }),

                        Select::make('kategory')
                            ->label('Kategory')
                            ->disabled()
                            ->native(false)
                            ->options([
                                'promosi' => 'promosi',
                                'mutasi' => 'mutasi'
                            ])
                            ->reactive()
                            ->placeholder('')
                            ->dehydrated(false),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->displayFormat('d-M-Y')
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && $get('kontrak_lanjutan') == 0) {
                                    $set('end_date', Carbon::parse($state)->addMonths(9)->format('Y-m-d H:i:s'));
                                }
                            })
                            ->dehydrated(false),

                        DatePicker::make('end_date')
                            ->displayFormat('d-M-Y')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),

                        FileUpload::make('file')
                            ->label('Upload File Mutasi/Promosi')
                            ->columnSpan(1)
                            ->disk('public')
                            ->downloadable()
                            ->preserveFilenames()
                            ->directory('crew/mutasi')
                            ->columnSpanFull()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->required(),
                    ]),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultPaginationPageOption('5')
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->recordTitleAttribute('nomor_dokumen')
            ->columns([
                TextColumn::make('nomor_dokumen')
                    ->label('Nomor'),
                TextColumn::make('perusahaan.kode_perusahaan')
                    ->searchable(),
                TextColumn::make('jabatan.kode_jabatan')
                    ->searchable(),
                TextColumn::make('wilayah.kode_wilayah')
                    ->searchable(),
                TextColumn::make('kapal.nama_kapal')
                    ->searchable(),
                TextColumn::make('kontrak_lanjutan')
                    ->label('Jenis Kontrak')
                    ->formatStateUsing(fn($state) => $state == true ? 'Lanjutan' : 'Baru'),
                TextColumn::make('start_date')
                    ->date('d-M-Y'),
                TextColumn::make('end_date')
                    ->date('d-M-Y'),
                TextColumn::make('status_kontrak')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'waiting approval' => 'warning',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('generate_document_mutasi_promosi')
                    ->button()
                    ->label('Generate dokumen')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-printer')
                    ->modalDescription('generate dokumen untuk kontrak ini')
                    ->modalWidth(Width::Small)
                    ->hidden(fn($record) => $record->kategory === 'signon' || $record->status_kontrak == 'active' || $record->status_kontrak == 'expired')
                    ->before(fn($record,  $action) => redirect()->route('generate.promosi', [
                        'id' => $this->ownerRecord->id
                    ]))
                    ->after(fn($record, $action) => $action->cancel()),
                EditAction::make()
                    ->button()
                    ->slideOver()
                    ->hidden(fn($record) => $record->kategory === 'signon' || $record->status_kontrak == 'expired')
                    ->after(function ($record) {
                        if (!empty($record->file)) {
                            $record->crew()->update(['status' => 'active']);
                            $record->update(['status_kontrak' => 'active']);
                            Log::info($record->kontrak_lanjutan);
                            if ($record->kontrak_lanjutan == 1) {
                                $oldContract = CrewKontrak::where('id_crew', $record->id_crew)
                                    ->orderBy('created_at', 'desc')
                                    ->skip(1)->first();
                                if ($oldContract) {
                                    $oldContract->update([
                                        'end_date' => Carbon::parse($record->start_date)->copy()->subDay()->format('Y-m-d'),
                                    ]);
                                }
                            }
                        }
                    }),
                DeleteAction::make()->button(),

            ])
            ->toolbarActions([]);
    }
}
