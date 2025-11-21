<?php

namespace App\Filament\Crew\Resources\CrewSignoffs\Schemas;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Models\AlasanBerhenti;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CrewSignoffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kontrak Info')
                    ->columnSpan(2)
                    ->columns(['sm' => 1, 'md' => 2, 'lg' => 3, 'xl' => 3])
                    ->description('Detail kontrak crew')
                    ->icon('heroicon-m-user')
                    ->headerActions([
                        Action::make('Selengkapnya')
                            ->url(fn($record) => AllCrewResource::getUrl('detail_kontrak', ['record' => $record->lastKontrak->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        TextEntry::make('nomor_dokumen')
                            ->state(fn($record): string => $record->lastKontrak?->nomor_dokumen ?? '-'),

                        TextEntry::make('nama')
                            ->state(fn($record): string => $record->lastKontrak?->crew?->nama_crew ?? '-'),

                        TextEntry::make('perusahaan')
                            ->state(fn($record): string => $record->lastKontrak?->perusahaan?->nama_perusahaan ?? '-'),

                        TextEntry::make('jabatan')
                            ->state(
                                fn($record): string =>
                                $record->lastKontrak?->jabatan
                                    ? ($record->lastKontrak->jabatan->golongan . '-' . $record->lastKontrak->jabatan->nama_jabatan)
                                    : '-'
                            ),
                        TextEntry::make('tanggal_mulai')
                            ->state(fn($record): string => $record->lastKontrak?->start_date ?? '-'),

                        TextEntry::make('tanggal_selesai')
                            ->state(fn($record): string => $record->lastKontrak?->end_date ?? '-'),
                    ]),

                Section::make('')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Sign Off')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->required()
                            ->columnSpan(1)
                            ->dehydrated(false),
                        Select::make('id_alasan')
                            ->label('Alasan Berhenti')
                            ->required()
                            ->searchable()
                            ->columnSpan(1)
                            ->native(false)
                            ->preload()
                            ->placeholder('')
                            ->options(AlasanBerhenti::pluck('nama_alasan', 'id'))
                            ->dehydrated(false),
                        Textarea::make('keterangan')
                            ->columnSpan(2)
                            ->dehydrated(false),
                       
                    ])

            ]);
    }
}
