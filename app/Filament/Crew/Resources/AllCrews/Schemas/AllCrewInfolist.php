<?php

namespace App\Filament\Crew\Resources\AllCrews\Schemas;

use App\Filament\Crew\Resources\AllCrews\Pages\HistoryInterview;
use App\Filament\Crew\Resources\AllCrews\Pages\HistoryMutasiPromosi;
use App\Filament\Crew\Resources\AllCrews\Pages\HistorySignOff;
use App\Filament\Crew\Resources\AllCrews\Pages\HistorySignOn;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AllCrewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Actions::make([
                    Action::make('history_interview')
                        ->label('History interview')
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->url(fn($record) => HistoryInterview::getUrl(['record' => $record])),
                    Action::make('history_signon')
                        ->label('History sign on')
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->url(fn($record) => HistorySignOn::getUrl(['record' => $record])),
                    Action::make('history_mutasi')
                        ->label('History mutasi promosi')
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->url(fn($record) => HistoryMutasiPromosi::getUrl(['record' => $record])),
                    Action::make('history_signoff')
                        ->label('History sign off')
                        ->icon('heroicon-o-briefcase')
                        ->color('info')
                        ->url(fn($record) => HistorySignOff::getUrl(['record' => $record])),
                ])
                    ->columnSpanFull(),
                Tabs::make('Data Crew')
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 3
                    ])
                    ->tabs([
                        Tab::make('Biodata')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('nama_crew')->label('Nama Lengkap'),
                                    TextEntry::make('agama')->label('Agama'),
                                    TextEntry::make('kebangsaan')
                                        ->label('Kebangsaan')
                                        ->formatStateUsing(fn($record) => $record->suku . ' ' . $record->kebangsaan),
                                    TextEntry::make('status_identitas')->label('Status Identitas'),
                                ])->columnSpan(2),

                                ImageEntry::make('avatar')->label('Foto Crew')->columnSpan(1)->disk('public'),

                                TextEntry::make('tempat_lahir')->label('Tempat Lahir'),
                                TextEntry::make('tanggal_lahir')->label('Tanggal Lahir'),
                                TextEntry::make('tempat_lahir')
                                    ->label('Usia')
                                    ->formatStateUsing(fn($record) => $record->tanggal_lahir
                                        ? Carbon::parse($record->tanggal_lahir)->age . ' Tahun'
                                        : '-'),

                                TextEntry::make('golongan_darah')->label('Golongan Darah'),
                                TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                                TextEntry::make('status')->label('Status')->badge()->color('info'),
                            ])
                            ->icon('heroicon-o-user')
                            ->columnSpanFull()
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 3
                            ]),

                        Tab::make('Kontak')
                            ->schema([
                                TextEntry::make('email')->label('Email'),
                                TextEntry::make('no_hp')->label('No. HP'),
                                TextEntry::make('no_telp_rumah')->label('No. Telepon Rumah'),
                                TextEntry::make('alamat_sekarang')->label('Alamat Sekarang'),
                                TextEntry::make('alamat_ktp')->label('Alamat KTP'),
                                TextEntry::make('status_rumah')->label('Status Tempat Tinggal'),
                            ])
                            ->icon('heroicon-o-phone')
                            ->columnSpanFull()
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 3
                            ]),

                        Tab::make('Fisik')
                            ->schema([
                                TextEntry::make('tinggi_badan')->label('Tinggi Badan')->badge()->color('success')->formatStateUsing(fn($state) => $state . ' CM'),
                                TextEntry::make('berat_badan')->label('Berat Badan')->badge()->color('success')->formatStateUsing(fn($state) => $state . ' KG'),
                                TextEntry::make('ukuran_waerpack')->label('Ukuran Wearpack')->badge()->color('success'),
                                TextEntry::make('ukuran_sepatu')->label('Ukuran Sepatu')->badge()->color('success'),
                            ])
                            ->icon('heroicon-o-scale')
                            ->columnSpanFull()
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 3
                            ]),

                        Tab::make('Keluarga')
                            ->schema([
                                RepeatableEntry::make('nok')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('nama')->label('Nama'),
                                        TextEntry::make('hubungan')->label('Hubungan'),
                                        TextEntry::make('no_hp')->label('No Telepon'),
                                        TextEntry::make('alamat')->label('Alamat')->columnSpanFull(),
                                    ])
                                    ->grid(2)
                                    ->columns(3)
                                    ->columnSpanFull()
                            ])
                            ->icon('heroicon-o-users')
                            ->columnSpanFull(),


                    ])
            ]);
    }
}
