<?php

namespace App\Filament\StaffCrew\Resources\CrewOverviewResource\Pages;

use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use App\Filament\StaffCrew\Resources\CrewOverviewResource\RelationManagers\CrewPklRelationManager;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
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
use Filament\Resources\Pages\Page;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Carbon;

class ViewCrewOverview extends ViewRecord
{
    protected static string $resource = CrewOverviewResource::class;
    protected static ?string $navigationLabel = 'Detail Crew';

    public function getTitle(): string
    {
        return 'Detail ' . $this->record->nama_crew;;
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('Data Crew')->tabs([

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

                        ImageEntry::make('foto')->label('Foto Crew')->columnSpan(1),

                        TextEntry::make('tempat_lahir')->label('Tempat Lahir'),
                        TextEntry::make('tanggal_lahir')->label('Tanggal Lahir'),
                        TextEntry::make('tempat_lahir')
                            ->label('Usia')
                            ->formatStateUsing(fn($record) => $record->tanggal_lahir
                                ? Carbon::parse($record->tanggal_lahir)->age . ' Tahun'
                                : '-'),

                        TextEntry::make('golongan_darah')->label('Golongan Darah'),
                        TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                        TextEntry::make('status_proses')->label('Status Proses')->badge()->color('info'),
                    ])
                    ->icon('heroicon-o-user')
                    ->columnSpanFull()
                    ->columns(3),

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
                    ->columns(3),

                Tab::make('Fisik')
                    ->schema([
                        TextEntry::make('tinggi_badan')->label('Tinggi Badan')->badge()->color('success')->formatStateUsing(fn($state) => $state . ' CM'),
                        TextEntry::make('berat_badan')->label('Berat Badan')->badge()->color('success')->formatStateUsing(fn($state) => $state . ' KG'),
                        TextEntry::make('ukuran_waerpack')->label('Ukuran Wearpack')->badge()->color('success'),
                        TextEntry::make('ukuran_sepatu')->label('Ukuran Sepatu')->badge()->color('success'),
                    ])
                    ->icon('heroicon-o-scale')
                    ->columnSpanFull()
                    ->columns(3),

                Tab::make('Keluarga')
                    ->schema([
                        TextEntry::make('nok_nama')->label('Nama'),
                        TextEntry::make('nok_hubungan')->label('Hubungan'),
                        TextEntry::make('nok_hp')->label('No. Telepon'),
                        TextEntry::make('nok_alamat')->label('Alamat')->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-users')
                    ->columnSpanFull()
                    ->columns(3),
            ])->columnSpanFull()->columns(3)
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
                ->url(fn($record) => CrewOverviewResource::getUrl('edit', ['record' => $record])),
            DeleteAction::make('delete')
                ->icon('heroicon-o-trash')
                ->label('Hapus Data')
        ];
    }
}
