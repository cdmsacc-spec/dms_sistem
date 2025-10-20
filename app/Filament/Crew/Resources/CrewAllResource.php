<?php

namespace App\Filament\Crew\Resources;

use App\Enums\StatusCrew;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Crew\Resources\CrewAllResource\Pages;
use App\Models\CrewApplicants;
use App\Models\Jabatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use App\Filament\Crew\Resources\CrewAllResource\RelationManagers\CrewCertificatesRelationManager;
use App\Filament\Crew\Resources\CrewAllResource\RelationManagers\CrewExperienceRelationManager;
use App\Filament\Crew\Resources\CrewAllResource\RelationManagers\CrewDocumentRelationManager;
use App\Filament\StaffCrew\Resources\CrewAllResource\Pages\ViewCrewOverview;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Str;

class CrewAllResource extends Resource
{

    protected static ?string $slug = 'crew-all';
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $pluralModelLabel = 'All Crew';
    protected static ?string $navigationLabel = 'All Crew';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 2;
    public static function getPermissionIdentifier(): ?string
    {
        return 'crew_all'; // akan menjadi dasar nama izin
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    // ==============================
                    // STEP 1: BIODATA
                    // ==============================
                    Wizard\Step::make('Biodata')
                        ->description('Data pribadi crew')
                        ->schema([
                            Grid::make(4)->schema([

                                // Kolom kiri (3/4) - Form Biodata & Fisik
                                Grid::make(3)
                                    ->columnSpan(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('posisi_dilamar')
                                            ->label('Posisi Dilamar')
                                            ->required(),

                                        Forms\Components\TextInput::make('nama_crew')
                                            ->label('Nama Lengkap')
                                            ->required(),

                                        Forms\Components\Select::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->native(false)
                                            ->options([
                                                'Laki Laki' => 'Laki Laki',
                                                'Perempuan' => 'Perempuan',
                                            ])
                                            ->required(),

                                        Forms\Components\TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->required(),

                                        Forms\Components\DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->prefixIcon('heroicon-m-calendar')
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\Select::make('agama')
                                            ->label('Agama')
                                            ->native(false)
                                            ->searchable()
                                            ->options([
                                                'Islam' => 'Islam',
                                                'Protestan' => 'Protestan',
                                                'Katolik' => 'Katolik',
                                                'Buddha' => 'Buddha',
                                                'Hindu' => 'Hindu',
                                                'Konghucu' => 'Konghucu',
                                                'Lain - Lain' => 'Lain - Lain',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('golongan_darah')
                                            ->label('Golongan Darah')
                                            ->native(false)
                                            ->options(['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O'])
                                            ->required(),

                                        Forms\Components\Select::make('status_identitas')
                                            ->label('Status Identitas')
                                            ->native(false)
                                            ->searchable()
                                            ->options([
                                                'Lajang [TK0]' => 'Lajang [TK0]',
                                                'Menikah [TK0]' => 'Menikah [TK0]',
                                                'Menikah [TK1] Anak 1' => 'Menikah [TK1] Anak 1',
                                                'Menikah [TK2] Anak 2' => 'Menikah [TK2] Anak 2',
                                                'Menikah [TK3] Anak 3' => 'Menikah [TK3] Anak 3',
                                                'Duda [TK0]' => 'Duda [TK0]',
                                                'Duda [TK1] Anak 1' => 'Duda [TK1] Anak 1',
                                                'Duda [TK2] Anak 2' => 'Duda [TK2] Anak 2',
                                            ])
                                            ->required(),

                                        Forms\Components\TextInput::make('kebangsaan'),
                                        Forms\Components\TextInput::make('suku'),

                                        Forms\Components\TextInput::make('berat_badan')
                                            ->label('Berat Badan')
                                            ->numeric()
                                            ->suffix('KG')
                                            ->required(),

                                        Forms\Components\TextInput::make('tinggi_badan')
                                            ->label('Tinggi Badan')
                                            ->numeric()
                                            ->suffix('CM')
                                            ->required(),

                                        Forms\Components\TextInput::make('ukuran_sepatu')
                                            ->label('Ukuran Sepatu')
                                            ->numeric()
                                            ->required(),

                                        Forms\Components\TextInput::make('ukuran_waerpack')
                                            ->label('Ukuran Wearpack')
                                            ->required(),
                                    ]),

                                // Kolom kanan (1/4) - Foto Crew
                                Forms\Components\FileUpload::make('foto')
                                    ->image()
                                    ->maxSize(10240)
                                    ->imageEditor()
                                    ->disk('public')
                                    ->panelLayout('integrated')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadButtonPosition('center')
                                    ->uploadProgressIndicatorPosition('center')
                                    ->directory('crew/foto')
                                    ->openable()
                                    ->panelAspectRatio('1:1')
                                    ->columnSpan(1),
                            ]),
                        ]),

                    // ==============================
                    // STEP 2: CONTACT
                    // ==============================
                    Wizard\Step::make('Contact')
                        ->description('Kontak dari crew')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('email')->label('Email')->email()->required(),
                                    Forms\Components\TextInput::make('no_hp')->label('No. HP')->numeric()->required(),
                                    Forms\Components\TextInput::make('no_telp_rumah')->label('No. Telp Rumah')->numeric()->nullable(),
                                    Forms\Components\Textarea::make('alamat_ktp')->label('Alamat KTP')->columnSpan(1),
                                    Forms\Components\Textarea::make('alamat_sekarang')->label('Alamat Sekarang')->columnSpan(2),
                                ]),
                            Forms\Components\Fieldset::make('Status Rumah')->schema([
                                Forms\Components\Radio::make('status_rumah')
                                    ->label('')
                                    ->options([
                                        'Milik Sendiri' => 'Milik Sendiri',
                                        'Sewa / Kontrak' => 'Sewa / Kontrak',
                                        'Orang Tua' => 'Orang Tua',
                                        'Lain Lain' => 'Lain Lain',
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull(),
                            ])
                        ]),

                    // ==============================
                    // STEP 3: NEXT OF KIN
                    // ==============================
                    Wizard\Step::make('Next of Kin')
                        ->description('Informasi keluarga crew')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('nok_nama')->label('Nama'),
                                Forms\Components\TextInput::make('nok_hubungan')->label('Hubungan'),
                                Forms\Components\TextInput::make('nok_hp')->label('No Telepon'),
                                Forms\Components\Textarea::make('nok_alamat')->label('Alamat')->columnSpanFull(),
                            ])
                        ]),
                ])
                    ->nextAction(fn(Action $action) => $action->label('Next step'))
                    ->columnSpanFull(),

              
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Tidak Ada Data')
            ->emptyStateDescription('belum ada data ditambahkan')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular(),
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
                    ->label('Status')
                    ->searchable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        StatusCrew::Draft->value => 'info',
                        StatusCrew::ReadyForInterview->value => 'warning',
                        StatusCrew::Inactive->value => 'danger',
                        StatusCrew::Standby->value => 'primary',
                        StatusCrew::Active->value => 'success',
                        StatusCrew::Rejected->value => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status_proses')
                    ->label('Status')
                    ->native(false)
                    ->options(
                        StatusCrew::options()
                    ),
                \Filament\Tables\Filters\SelectFilter::make('jabatan')
                    ->label('Jabatan')
                    ->native(false)
                    ->options(function () {
                        return Jabatan::get()->pluck('golongan', 'golongan')->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (empty($data['value'])) return;

                        $query->whereHas('crewPkl', function ($q) use ($data) {
                             $q->where('status_kontrak', 'Active')
                                ->whereHas('jabatan', function ($q2) use ($data) {
                                $q2->where('golongan', $data['value']);
                            });
                        });
                    }),
                \Filament\Tables\Filters\SelectFilter::make('perusahaaan')
                    ->label('Perusahaan')
                    ->native(false)
                    ->options(function () {
                        return Perusahaan::get()->pluck('nama_perusahaan', 'nama_perusahaan')->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (empty($data['value'])) return;

                        $query->whereHas('crewPkl', function ($q) use ($data) {
                             $q->where('status_kontrak', 'Active')
                                ->whereHas('perusahaan', function ($q2) use ($data) {
                                $q2->where('nama_perusahaan', $data['value']);
                            });
                        });
                    }),

                Tables\Filters\Filter::make('usia')
                    ->label('Usia')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('age')
                            ->label('Usia')
                            ->numeric()
                            ->placeholder('Enter age'),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['age'])) {
                            return;
                        }

                        $query->whereRaw(
                            'EXTRACT(YEAR FROM AGE(current_date, tanggal_lahir)) = ?',
                            [$data['age']]
                        );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->columnSpan(2)
                    ->columns(2)
                    ->form([
                        DatePicker::make('Dari tanggal ditambahkan')->columnSpan(1)
                            ->native(false),

                        DatePicker::make('Sampai tanggal ditambahkan')->columnSpan(1)
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Dari tanggal ditambahkan'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                                    ->when(
                                        $data['Sampai tanggal ditambahkan'],
                                        fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                                    )
                            );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                        ->color('success'),
                ])
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
            CrewDocumentRelationManager::class,
            CrewCertificatesRelationManager::class,
            CrewExperienceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrewAlls::route('/'),
            'create' => Pages\CreateCrewAll::route('/create'),
            'edit' => Pages\EditCrewAll::route('/{record}/edit'),
            'view' => Pages\ViewCrewOverview::route('/{record}'),
            'interview' =>   Pages\SubNavigation\Interview::route('/{record}/interview'),
            'signon' =>   Pages\SubNavigation\SignOn::route('/{record}/signon'),
            'signoff' =>   Pages\SubNavigation\SignOff::route('/{record}/signoff'),
            'promosi' =>   Pages\SubNavigation\Promosi::route('/{record}/promosi'),
            'detail_pkl' => Pages\SubNavigation\Detail\DetailKontrakPkl::route('/{record}/detail_kontak_pkl'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return collect($page->generateNavigationItems([
            Pages\ViewCrewOverview::class,
            Pages\SubNavigation\Interview::class,
            Pages\SubNavigation\SignOn::class,
            Pages\SubNavigation\Promosi::class,
            Pages\SubNavigation\SignOff::class,
        ]))->map(function ($item) use ($page) {
            return $item->isActiveWhen(
                fn() =>
                Str::startsWith(url()->current(), $item->getUrl())
            );
        })->all();
    }
}
