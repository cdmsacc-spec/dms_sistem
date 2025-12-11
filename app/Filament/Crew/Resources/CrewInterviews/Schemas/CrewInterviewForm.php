<?php

namespace App\Filament\Crew\Resources\CrewInterviews\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CrewInterviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Crew')
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 4
                    ])
                    ->schema([
                        TextEntry::make('nama')
                            ->state(fn($record): string => $record->nama_crew),
                        TextEntry::make('Posisi Dilamar')
                            ->state(fn($record): string => $record->posisi_dilamar),
                        TextEntry::make('Kebangsaan')
                            ->state(fn($record): string => $record->kebangsaan),
                        TextEntry::make('Alamat')
                            ->state(fn($record): string => $record->alamat_ktp),
                    ]),

                Section::make('')
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 4
                    ])
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Interview')
                            ->columnSpan(4)
                            ->displayFormat('d-M-Y')
                            ->prefixIcon('heroicon-m-calendar')
                            ->native(false)
                            ->required()
                            ->dehydrated(false),
                        Textarea::make('crewing')
                            ->label('Crewing')
                            ->required()
                            ->columnSpan(2)
                            ->dehydrated(false),
                        Textarea::make('user_operation')
                            ->label('User Operation')
                            ->required()
                            ->columnSpan(2)
                            ->dehydrated(false),
                        Textarea::make('summary')
                            ->label('Summary')
                            ->required()
                            ->columnSpan(2)
                            ->dehydrated(false),
                        Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->required()
                            ->columnSpan(2)
                            ->dehydrated(false),
                        FileUpload::make('file')
                            ->label('Upload File Interview')
                            ->columnSpanFull()
                            ->required()
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->disk('public')
                            ->downloadable()
                            ->dehydrated(false)
                            ->directory('crew/interview')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ]),
                    ]),
            ]);
    }
}
