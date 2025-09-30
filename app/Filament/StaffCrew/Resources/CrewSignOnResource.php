<?php

namespace App\Filament\StaffCrew\Resources;

use App\Filament\StaffCrew\Resources\CrewSignOnResource\Pages;
use App\Filament\StaffCrew\Resources\CrewSignOnResource\RelationManagers;
use App\Filament\StaffCrew\Resources\CrewSignOnResource\RelationManagers\CrewPklRelationManager;
use App\Models\CrewApplicants;
use App\Models\Jabatan;
use App\Models\Lookup;
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

class CrewSignOnResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $navigationLabel = 'Sign On';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 5;

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
                            ->options(Jabatan::pluck('nama_jabatan', 'id'))
                            ->native(false)
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('berangkat_dari')
                            ->label('Berangkat Dari')
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
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Fieldset::make('Status Kontrak')->schema([
                            Forms\Components\Radio::make('status_kontrak')
                                ->label('Status Kontrak')
                                ->columnSpanFull()
                                ->columns(3)
                                ->default('Waiting Approval')
                                ->options([
                                    'Active' => 'Active',
                                    'Expired' => 'Expired',
                                    'Waiting Approval' => 'Waiting Approval'
                                ])
                                ->dehydrated(false),
                        ])
                    ]),

                // ==============================
                // SECTION STATUS CREW
                // ==============================
                Forms\Components\Section::make('Status Crew')
                    ->description('Status proses crew saat ini')
                    ->schema([
                        Forms\Components\Radio::make('status_proses')
                            ->label('Status Proses')
                            ->options([
                                'Draft' => 'Draft',
                                'Ready For Interview' => 'Ready For Interview',
                                'Standby' => 'Standby',
                                'Inactive' => 'Inactive',
                                'Active' => 'Active'
                            ])
                            ->required()
                            ->columns(5)
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', 'Standby'))
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
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
                Tables\Columns\TextColumn::make('status_identitas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_proses')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Draft' => 'info',
                        'Ready For Interview' => 'warning',
                        'Inactive' => 'danger',
                        'Standby' => 'info',
                        'Active' => 'success'
                    })->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->label('Sign On'),
                Tables\Actions\Action::make('detail')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->url(fn($record) => CrewOverviewResource::getUrl('view', ['record' => $record])),

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
            'index' => Pages\ListCrewSignOn::route('/'),
            'create' => Pages\CreateCrewSignOn::route('/create'),
            'edit' => Pages\EditCrewSignOn::route('/{record}/edit'),
        ];
    }
}
