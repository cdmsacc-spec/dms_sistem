<?php

namespace App\Filament\StaffCrew\Resources;

use App\Filament\StaffCrew\Resources\CrewCandidateResource\Pages;
use App\Filament\StaffCrew\Resources\CrewCandidateResource\RelationManagers;
use App\Models\CrewApplicants;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;

class CrewCandidateResource extends Resource
{
    protected static ?string $model = CrewApplicants::class;
    protected static ?string $navigationLabel = 'Candidates';
    protected static ?string $navigationGroup = 'Crew Management';
    protected static ?int $navigationSort = 3;

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
                                    ->imageEditor()
                                    ->disk('public')
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



                Section::make('Status')
                    ->schema([
                        Forms\Components\Radio::make('status_proses')
                            ->label('')
                            ->options([
                                'Draft' => 'Draft',
                                'Ready For Interview' => 'Ready For Interview',
                                'Standby' => 'Standby',
                                'Inactive' => 'Inactive',
                                'Active' => 'Active'
                            ])
                            ->required()
                            ->columns(5)
                            ->columnSpanFull(),
                    ])->visible(fn(?string $context) => $context === 'edit')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_proses', 'Draft'))
            ->emptyStateHeading('Tidak Ada Data')
            ->defaultSort('created_at', 'desc')
            ->emptyStateDescription('belum ada data ditambahkan')
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->color('info'),
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
            RelationManagers\CrewDocumentRelationManager::class,
            RelationManagers\CrewCertificatesRelationManager::class,
            RelationManagers\CrewExperienceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrewCandidates::route('/'),
            'create' => Pages\CreateCrewCandidates::route('/create'),
            'edit' => Pages\EditCrewCandidates::route('/{record}/edit'),
            'view' => Pages\ViewCrewCandidates::route('/{record}'),
        ];
    }
}
