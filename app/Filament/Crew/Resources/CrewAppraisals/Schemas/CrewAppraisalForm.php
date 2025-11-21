<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Schemas;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CrewAppraisalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Crew Info')
                    ->columnSpan(1)
                    ->columns(2)
                    ->description('Detail informasi crew')
                    ->icon('heroicon-m-user')
                    ->headerActions([
                        Action::make('Selengkapnya.')
                            ->color('info')
                            ->url(fn($record) => AllCrewResource::getUrl('view', ['record' => $record->crew->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        TextEntry::make('nama')
                            ->state(fn($record): string => $record->crew->nama_crew),
                        TextEntry::make('no_telepon')
                            ->state(fn($record): string => $record->crew->no_hp),
                        TextEntry::make('alamat')
                            ->extraAttributes([
                                'class' => 'truncate max-w-xs', // max-w-xs bisa diganti sesuai kebutuhan
                                'title' => $record->crew->alamat_sekarang ?? '', // biar kalau hover muncul tooltip full
                            ])
                            ->state(fn($record): string => $record->crew->alamat_sekarang),
                        TextEntry::make('jenis_kelamin')
                            ->state(fn($record): string => $record->crew->jenis_kelamin),
                    ]),

                Section::make('Kontrak Active')
                    ->columnSpan(1)
                    ->columns(2)
                    ->description('Detail informasi kontrak')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->headerActions([
                        Action::make('Selengkapnya')
                            ->color('info')
                            ->url(fn($record) => AllCrewResource::getUrl('detail_kontrak', ['record' => $record->id]))
                            ->openUrlInNewTab(false)
                    ])
                    ->schema([
                        TextEntry::make('nama_perusahaan')
                            ->state(fn($record): string => $record->perusahaan->nama_perusahaan),
                        TextEntry::make('appraisal_summary')
                            ->state(function ($record) { {
                                    $appraisals = $record->appraisal()->pluck('nilai');

                                    if ($appraisals->isEmpty()) {
                                        return 'Belum Ada Penilaian';
                                    }
                                    $average = round($appraisals->avg());
                                    return match (true) {
                                        $average >= 100 => "Sangat Memuaskan ($average)",
                                        $average >= 75  => "Memuaskan ($average)",
                                        $average >= 50  => "Cukup Memuaskan ($average)",
                                        $average >= 25  => "Tidak Memuaskan ($average)",
                                        default => "Belum Dinilai",
                                    };
                                }
                            }),
                        TextEntry::make('start_date')
                            ->state(fn($record): string => $record->start_date),
                        TextEntry::make('end_date')
                            ->state(fn($record): string => $record->end_date),
                    ]),

                Section::make('')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('aprraiser')
                            ->dehydrated(false)
                            ->required(),
                        Select::make('nilai')
                            ->placeholder('')
                            ->native(false)
                            ->options([
                                100 => "Sangat Memuaskan",
                                70 => 'Memuaskan',
                                50 => 'Cukup Memuaskan',
                                25 => 'Tidak Memuaskan',
                            ])
                            ->dehydrated(false)
                            ->required(),
                        Textarea::make('keterangan')
                            ->dehydrated(false)
                            ->columnSpanFull()
                    ])
            ]);
    }
}
