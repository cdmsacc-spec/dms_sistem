<?php

namespace App\Filament\Crew\Resources;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\MutasiPromosiResource\Pages;
use App\Filament\Crew\Resources\MutasiPromosiResource\RelationManagers;
use App\Filament\Crew\Resources\MutasiPromosiResource\RelationManagers\CrewPklRelationManager;
use App\Models\CrewApplicants;
use App\Models\Jabatan;
use App\Models\MutasiPromosi;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MutasiPromosiResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $slug = 'mutasi-promosi';
    protected static ?string $navigationLabel = 'Promosi';
    protected static ?string $pluralModelLabel = 'Promosi';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ==============================
                // SECTION PENEMPATAN
                // ==============================
                Forms\Components\Section::make('Penempatan Crew')
                    ->description('Informasi perusahaan, wilayah, kapal, dan jabatan crew')
                    ->columns(3)
                    ->schema([

                        Forms\Components\Select::make('perusahaan_id')
                            ->label('Pilih Perusahaan')
                            ->options(Perusahaan::pluck('nama_perusahaan', 'id'))
                            ->native(false)
                            ->dehydrated(false)
                            ->reactive()
                            ->columnSpan(2),

                        Forms\Components\Select::make('wilayah_id')
                            ->label('Wilayah Operasional')
                            ->options(WilayahOperasional::pluck('nama_wilayah', 'id'))
                            ->native(false)
                            ->dehydrated(false),
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
                            ->dehydrated(false),

                        Forms\Components\Select::make('jabatan_id')
                            ->label('Jabatan Crew')
                            ->options(Jabatan::all()
                                ->mapWithKeys(fn($jabatan) => [
                                    $jabatan->id => "{$jabatan->nama_jabatan} ({$jabatan->devisi} - {$jabatan->golongan})",
                                ]))
                            ->native(false)
                            ->dehydrated(false),

                        Forms\Components\Select::make('berangkat_dari')
                            ->label('Berangkat Dari')
                            ->options([
                                'Jakarta' => 'Jakarta',
                                'Lokal' => 'Lokal'
                            ])
                            ->native(false)
                            ->dehydrated(false),

                    ]),

                // ==============================
                // SECTION KONTRAK
                // ==============================
                Forms\Components\Section::make('Kontrak Crew')
                    ->description('Detail kontrak, gaji, tanggal mulai dan selesai, serta status kontrak')
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('gaji')
                            ->label('Gaji (Rp.)')
                            ->prefix('Rp.')
                            ->numeric()
                            ->dehydrated(false),

                        Forms\Components\Select::make('kontrak_lanjutan')
                            ->label('Kontrak')
                            ->native(false)
                            ->options([
                                false => 'Baru',
                                true => 'Lanjutan'
                            ])
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($record, $state, callable $set, callable $get) {
                                $oldContract = $record->crewPkl()
                                    ->latest('end_date')
                                    ->first();
                                if ($state == 1 && $oldContract) {
                                    $set('end_date', $oldContract->end_date);
                                } else if ($state == 0) {
                                    $set('end_date', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && $get('kontrak_lanjutan') == 0) {
                                    $set('end_date', Carbon::parse($state)->addMonths(9)->format('Y-m-d H:i:s'));
                                }
                            })
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('status_proses', StatusCrew::Active))
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->columns([
                Tables\Columns\TextColumn::make('nama_crew')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_proses')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        StatusCrew::Draft->value => 'info',
                        StatusCrew::ReadyForInterview->value => 'warning',
                        StatusCrew::Inactive->value => 'danger',
                        StatusCrew::Standby->value => 'primary',
                        StatusCrew::Active->value => 'success',
                        default => 'secondary',
                    })->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('Promosi'),
                Tables\Actions\Action::make('detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->url(fn($record) => CrewAllResource::getUrl('view', ['record' => $record])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CrewPklRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMutasiPromosis::route('/'),
            'create' => Pages\CreateMutasiPromosi::route('/create'),
            'edit' => Pages\EditMutasiPromosi::route('/{record}/edit'),
        ];
    }
}
