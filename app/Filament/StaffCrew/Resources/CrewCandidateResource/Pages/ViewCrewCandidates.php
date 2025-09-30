<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\Pages;

use App\Filament\StaffCrew\Resources\CrewCandidateResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ContentTabPosition;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Carbon;

class ViewCrewCandidates extends ViewRecord
{
    protected static string $resource = CrewCandidateResource::class;

    public function getTitle(): string
    {
        return 'Detail ' . $this->record->nama_crew;;
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(5)->schema([

                    // -----------------------
                    // BIODATA CREW
                    // -----------------------
                    Section::make('Biodata Crew')
                        ->columnSpan(3)
                        ->icon('heroicon-o-user')
                        ->schema([

                            Grid::make(3)
                                ->columnSpan(3)
                                ->schema([
                                    TextEntry::make('nama_crew')->label('Nama'),
                                    TextEntry::make('agama')->label('Agama'),
                                    TextEntry::make('kebangsaan')
                                        ->label('Kebangsaan')
                                        ->formatStateUsing(fn($record) => $record->suku . ' ' . $record->kebangsaan),
                                    TextEntry::make('tempat_lahir')->label('Tempat Lahir'),
                                    TextEntry::make('tanggal_lahir')->label('Tanggal Lahir'),
                                    TextEntry::make('created_at')
                                        ->label('Usia')
                                        ->formatStateUsing(fn($record) => $record->tanggal_lahir
                                            ? Carbon::parse($record->tanggal_lahir)->age . ' Tahun'
                                            : '-'),
                                    TextEntry::make('status_identitas')->label('Status'),
                                    TextEntry::make('golongan_darah')->label('Golongan Darah'),
                                    TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                                ]),

                            // Kolom kanan (1/4) - foto crew
                            ImageEntry::make('foto')
                                ->label('Foto Crew')
                                ->height(200)
                                ->columnSpan(1),
                        ]),

                    // -----------------------
                    // CONTACT CREW
                    // -----------------------
                    Section::make('Contact Crew')
                        ->columnSpan(2)
                        ->icon('heroicon-o-phone')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('email')->label('Email'),
                            TextEntry::make('no_hp')->label('No HP'),
                            TextEntry::make('no_telp_rumah')->label('Telp Rumah'),
                            TextEntry::make('status_rumah')->label('Status Rumah'),
                            TextEntry::make('alamat_sekarang')->label('Alamat Sekarang'),
                            TextEntry::make('alamat_ktp')->label('Alamat KTP'),
                        ]),
                ]),

                // -----------------------
                // FISIK & STATUS PROSES
                // -----------------------
                Section::make('Fisik & Status')
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        TextEntry::make('tinggi_badan')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn($state) => $state . ' CM'),
                        TextEntry::make('berat_badan')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn($state) => $state . ' KG'),
                        TextEntry::make('ukuran_waerpack')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('ukuran_sepatu')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('status_proses')
                            ->badge()
                            ->color('info'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Data')
                ->color('info')
                ->icon('heroicon-o-pencil')
                ->size(ActionSize::Small)
                ->url(fn($record) => CrewCandidateResource::getUrl('edit', ['record' => $record])),
            DeleteAction::make('delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->label('Hapus Data')
        ];
    }
}
