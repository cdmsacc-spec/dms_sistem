<?php

namespace App\Filament\StaffCrew\Resources\CrewPromosiResource\Pages;

use App\Filament\StaffCrew\Resources\CrewPromosiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Carbon;

class ViewCrewPromosi extends ViewRecord
{
    protected static string $resource = CrewPromosiResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(5)->schema([
                    Section::make('Biodata Crew')
                        ->columnSpan(3)
                        ->icon('heroicon-o-user')
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    Grid::make(3)
                                        ->columnSpan(3)
                                        ->schema([
                                            TextEntry::make('nama_crew')
                                                ->label('Nama'),
                                            TextEntry::make('agama')
                                                ->label('Agama'),
                                            TextEntry::make('kebangsaan')
                                                ->label('Kebangsaan')
                                                ->formatStateUsing(fn($record) => $record->suku . ' ' . $record->kebangsaan),
                                            TextEntry::make('tempat_lahir')
                                                ->label('Tempat Lahir'),
                                            TextEntry::make('tanggal_lahir')
                                                ->label('Tanggal Lahir'),
                                            TextEntry::make('tempat_lahir')
                                                ->label('Usia')
                                                ->formatStateUsing(fn($record) => $record->tanggal_lahir
                                                    ? Carbon::parse($record->tanggal_lahir)->age . ' Tahun'
                                                    : '-'),
                                            TextEntry::make('status_identitas')
                                                ->label('Status'),
                                            TextEntry::make('golongan_darah')
                                                ->label('Golongan Darah'),
                                            TextEntry::make('jenis_kelamin')
                                                ->label('Jenis Kelamin'),

                                        ]),
                                    ImageEntry::make('foto')
                                        ->label('')
                                        ->height(200)
                                ])
                        ]),
                    Section::make('Contact Crew')
                        ->columnSpan(2)
                        ->columns(2)
                        ->icon('heroicon-o-phone')
                        ->schema([
                            TextEntry::make('email'),
                            TextEntry::make('no_hp'),
                            TextEntry::make('no_telp_rumah'),
                            TextEntry::make('alamat_sekarang'),
                            TextEntry::make('alamat_ktp'),
                            TextEntry::make('status_rumah'),

                        ])
                ]),
            ]);
    }
}
