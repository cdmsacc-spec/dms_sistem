<?php

namespace App\Filament\Crew\Resources\CrewMutasis\Schemas;

use App\Models\Jabatan;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Log;

class CrewMutasiForm
{
    public static function configure(Schema $schema): Schema
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
                            ->dehydrated(false)
                            ->reactive()
                            ->columnSpan(2)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->perusahaan->nama_perusahaan)
                            ->afterStateUpdated(function ($set, $state) {
                                if (empty($state)) {
                                    $set('id_kapal', null);
                                }
                            }),

                        Select::make('id_wilayah')
                            ->label('Wilayah Operasional')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options(WilayahOperasional::pluck('nama_wilayah', 'id'))
                            ->native(false)
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->wilayah->nama_wilayah),

                        Select::make('id_kapal')
                            ->label('Nama Kapal')
                            ->placeholder('')
                            ->preload()
                            ->reactive()
                            ->live()
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
                            ->native(false)
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->kapal->nama_kapal),


                        Select::make('id_jabatan')
                            ->label('Jabatan Crew')
                            ->options(Jabatan::all()
                                ->mapWithKeys(fn($jabatan) => [
                                    $jabatan->id => "{$jabatan->nama_jabatan} ({$jabatan->devisi} - {$jabatan->golongan})",
                                ]))
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->jabatan->nama_jabatan),

                        Select::make('berangkat_dari')
                            ->label('Berangkat Dari')
                            ->placeholder('')
                            ->searchable()
                            ->preload()
                            ->options([
                                'Jakarta' => 'Jakarta',
                                'Lokal' => 'Lokal'
                            ])
                            ->native(false)
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->berangkat_dari),
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
                            ->dehydrated(true)
                            ->helperText(fn($record) => 'Old Rp.' . $record->lastKontrak->gaji),

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
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->start_date),

                        DatePicker::make('end_date')
                            ->displayFormat('d-M-Y')
                            ->label('Tanggal Selesai Kontrak')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(fn($record) => 'Old ' . $record->lastKontrak->end_date),
                    ]),
            ]);
    }
}
