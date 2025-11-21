<?php

namespace App\Filament\Crew\Resources\AllCrews\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class AllCrewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    // ==============================
                    // STEP 1: BIODATA
                    // ==============================
                    Step::make('Biodata')
                        ->description('Data pribadi crew')
                        ->schema([
                            Grid::make([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 4
                            ])->schema([

                                // Kolom kiri (3/4) - Form Biodata & Fisik
                                Grid::make([
                                    'sm' => 1,
                                    'md' => 2,
                                    'lg' => 2,
                                    'xl' => 3
                                ])
                                    ->columnSpan([
                                        'sm' => 4,
                                        'md' => 4,
                                        'lg' => 3,
                                        'xl' => 3
                                    ])
                                    ->schema([
                                        TextInput::make('posisi_dilamar')
                                            ->label('Posisi Dilamar')
                                            ->required(),

                                        TextInput::make('nama_crew')
                                            ->label('Nama Lengkap')
                                            ->required(),

                                        Select::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->placeholder('')
                                            ->native(false)
                                            ->options([
                                                'Laki Laki' => 'Laki Laki',
                                                'Perempuan' => 'Perempuan',
                                            ])
                                            ->required(),

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->required(),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->prefixIcon('heroicon-m-calendar')
                                            ->native(false)
                                            ->required(),

                                        Select::make('agama')
                                            ->label('Agama')
                                            ->placeholder('')
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

                                        Select::make('golongan_darah')
                                            ->label('Golongan Darah')
                                            ->placeholder('')
                                            ->native(false)
                                            ->options(['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O'])
                                            ->required(),

                                        Select::make('status_identitas')
                                            ->label('Status Identitas')
                                            ->placeholder('')
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

                                        TextInput::make('kebangsaan'),
                                        TextInput::make('suku'),

                                        TextInput::make('berat_badan')
                                            ->label('Berat Badan')
                                            ->numeric()
                                            ->suffix('KG')
                                            ->required(),

                                        TextInput::make('tinggi_badan')
                                            ->label('Tinggi Badan')
                                            ->numeric()
                                            ->suffix('CM')
                                            ->required(),

                                        TextInput::make('ukuran_sepatu')
                                            ->label('Ukuran Sepatu')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('ukuran_waerpack')
                                            ->label('Ukuran Wearpack')
                                            ->required(),
                                    ]),

                                // Kolom kanan (1/4) - Foto Crew
                                FileUpload::make('avatar')
                                    ->image()
                                    ->maxSize(10240)
                                    ->imageEditor()
                                    ->disk('public')
                                    ->panelLayout('integrated')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadButtonPosition('center')
                                    ->uploadProgressIndicatorPosition('center')
                                    ->directory('crew/avatar')
                                    ->openable()
                                    ->panelAspectRatio('1:1')
                                    ->columnSpan(1),
                            ]),
                        ]),

                    // ==============================
                    // STEP 2: CONTACT
                    // ==============================
                    Step::make('Contact')
                        ->description('Kontak dari crew')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('email')->label('Email')->email()->required(),
                                    TextInput::make('no_hp')->label('No. HP')->numeric()->required(),
                                    TextInput::make('no_telp_rumah')->label('No. Telp Rumah')->numeric()->nullable(),
                                    Textarea::make('alamat_ktp')->label('Alamat KTP')->columnSpan(1),
                                    Textarea::make('alamat_sekarang')->label('Alamat Sekarang')->columnSpan(2),
                                ]),
                            Fieldset::make('Status Rumah')->schema([
                                Radio::make('status_rumah')
                                    ->label('')
                                    ->options([
                                        'Milik Sendiri' => 'Milik Sendiri',
                                        'Sewa / Kontrak' => 'Sewa / Kontrak',
                                        'Orang Tua' => 'Orang Tua',
                                        'Lain Lain' => 'Lain Lain',
                                    ])
                                    ->columns([
                                        'sm' => 2,
                                        'md' => 3,
                                        'lg' => 3,
                                        'xl' => 4
                                    ])
                                    ->columnSpanFull(),
                            ])
                        ]),

                    // ==============================
                    // STEP 3: NEXT OF KIN
                    // ==============================
                    Step::make('Next of Kin')
                        ->description('Informasi keluarga crew')
                        ->schema([
                            Repeater::make('nok')
                                ->relationship('nok')
                                ->addActionLabel('Add Hubungan')
                                ->columnSpan(2)
                                ->addActionAlignment(Alignment::Start)
                                ->table([
                                    TableColumn::make('Nama')->markAsRequired(),
                                    TableColumn::make('Hubungan')->markAsRequired(),
                                    TableColumn::make('Telepon')->markAsRequired(),
                                    TableColumn::make('Alamat')->markAsRequired()
                                ])
                                ->schema([
                                    TextInput::make('nama')->label('Nama'),
                                    TextInput::make('hubungan')->label('Hubungan'),
                                    TextInput::make('no_hp')->label('No Telepon'),
                                    TextInput::make('alamat')->label('Alamat')->columnSpanFull(),
                                ])
                        ]),
                ])
                    ->nextAction(fn(Action $action) => $action->label('Next step'))
                    ->columnSpanFull(),
            ]);
    }
}
