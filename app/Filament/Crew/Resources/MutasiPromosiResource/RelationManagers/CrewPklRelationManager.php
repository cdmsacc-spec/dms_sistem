<?php

namespace App\Filament\Crew\Resources\MutasiPromosiResource\RelationManagers;

use App\Enums\StatusKontrakCrew;
use Illuminate\Database\Eloquent\Model;

use App\Models\Jabatan;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class CrewPklRelationManager extends RelationManager
{
    protected static string $relationship = 'crewPkl';
    protected $listeners = ['refresh' => '$refresh'];
 


    public function form(Form $form): Form
    {
        return $form
            ->schema([

                // ==============================
                // SECTION PENEMPATAN
                // ==============================
                Forms\Components\Section::make('Penempatan Crew')
                    ->description('Isi informasi perusahaan, wilayah, kapal, jabatan, dan lokasi berangkat')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('perusahaan_id')
                            ->label('Pilih Perusahaan')
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->native(false)
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('wilayah_id')
                            ->label('Wilayah Operasional')
                            ->options(WilayahOperasional::pluck('nama_wilayah', 'id'))
                            ->native(false)
                            ->required(),

                        Forms\Components\Select::make('kapal_id')
                            ->label('Nama Kapal')
                            ->options(function (callable $get) {
                                $perusahaanId = $get('perusahaan_id');
                                if ($perusahaanId) {
                                    return NamaKapal::where('perusahaan_id', $perusahaanId)
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

                        Forms\Components\Select::make('jabatan_id')
                            ->label('Jabatan Crew')
                            ->options(Jabatan::pluck('nama_jabatan', 'id'))
                            ->native(false)
                            ->required(),

                        Forms\Components\TextInput::make('berangkat_dari')
                            ->label('Berangkat Dari')
                            ->dehydrated(false),
                    ]),

                // ==============================
                // SECTION KONTRAK
                // ==============================
                Forms\Components\Section::make('Kontrak Crew')
                    ->description('Isi detail kontrak, gaji, tanggal mulai & selesai, file Sign On, dan status kontrak')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomor_document')
                            ->label('Nomor Dokumen Kontrak')->dehydrated(false),

                        Forms\Components\TextInput::make('gaji')
                            ->label('Gaji (Rp.)')
                            ->prefix('Rp.')
                            ->numeric()
                            ->required(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('end_date', Carbon::parse($state)->addMonths(9)->format('Y-m-d H:i:s'));
                                } else {
                                    $set('end_date', null);
                                }
                            })
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('kontrak_lanjutan')
                            ->label('Jenis Kontrak')
                            ->native(false)
                            ->options([
                                false => 'Baru',
                                true => 'Lanjutan'
                            ])
                            ->hidden()
                            ->default(false)
                            ->required(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Mutasi/Promosi')
                            ->columnSpan(1)
                            ->disk('public')
                            ->preserveFilenames()
                            ->directory('crew/signon')
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->recordTitleAttribute('nomor_document')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')
                    ->label('Nomor'),
                Tables\Columns\TextColumn::make('perusahaan.kode_perusahaan'),
                Tables\Columns\TextColumn::make('jabatan.kode_jabatan'),
                Tables\Columns\TextColumn::make('wilayah.kode_wilayah'),
                Tables\Columns\TextColumn::make('kapal.nama_kapal'),
                Tables\Columns\TextColumn::make('kontrak_lanjutan')
                    ->label('Jenis Kontrak')
                    ->formatStateUsing(fn($state) => $state == true ? 'Lanjutan' : 'Baru'),
                Tables\Columns\TextColumn::make('start_date'),
                Tables\Columns\TextColumn::make('end_date'),
                Tables\Columns\TextColumn::make('status_kontrak')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Active' => 'success',
                        'Expired' => 'danger',
                        'Waiting Approval' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('end_date'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                    ->url(fn($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, shouldOpenInNewTab: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('generate_document_promosi')
                    ->button()
                    ->label('Generate Document Promosi')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn($record) => $record->kategory === 'Sign On')
                    ->action(function (Model $ownerRecord, array $data, $record, $livewire) {
                        return redirect()->route('generate.promosi', [
                            'id' => $this->ownerRecord->id,
                        ]);
                    }),
                Tables\Actions\EditAction::make()->button()
                    ->hidden(fn($record) => $record->kategory === 'Sign On')
                    ->slideOver()
                    ->after(function ($record) {
                        if (!empty($record->file_path)) {
                            $record->update(['status_kontrak' => StatusKontrakCrew::Active]);
                        }
                    }),
                Tables\Actions\DeleteAction::make()->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
