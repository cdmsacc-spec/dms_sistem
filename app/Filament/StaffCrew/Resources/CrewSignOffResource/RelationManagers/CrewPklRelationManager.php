<?php

namespace App\Filament\StaffCrew\Resources\CrewSignOffResource\RelationManagers;

use App\Models\Jabatan;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class CrewPklRelationManager extends RelationManager
{
    protected static string $relationship = 'crewPkl';
    protected static bool $isLazy = false;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->crewPkl->count();
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penempatan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('perusahaan_id')
                            ->label('Perusahaan')
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->native(false)
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('wilayah_id')
                            ->label('Wilayah')
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
                            ->label('Jabatan')
                            ->options(Jabatan::pluck('nama_jabatan', 'id'))
                            ->native(false)
                            ->required(),
                    ]),
                Forms\Components\Section::make('Kontrak')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomor_document'),
                        Forms\Components\TextInput::make('gaji')
                            ->prefix('Rp.')
                            ->numeric()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
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
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('kontrak_lanjutan')
                            ->label('Kontrak')
                            ->native(false)
                            ->options([
                                false => 'Baru',
                                true => 'Lanjutan'
                            ])
                            ->required(),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Sign On')
                            ->columnSpan(1)
                            ->disk('public')
                            ->preserveFilenames()
                            ->directory('crew/signon')
                            ->required(),
                        Forms\Components\Radio::make('status_kontrak')
                            ->columnSpanFull()
                            ->columns(3)
                            ->options([
                                'Active' => 'Active',
                                'Expired' => 'Expired',
                                'Waiting Approval' => 'Waiting Approval'
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
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
