<?php

namespace App\Filament\Resources\JenisKapals\Pages;

use App\Filament\Imports\JenisKapalImporter;
use App\Filament\Resources\JenisKapals\JenisKapalResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageJenisKapals extends ManageRecords
{
    protected static string $resource = JenisKapalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Jenis Kapal')
                ->visible(fn() => auth()->user()->can('create:jenis-kapal'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Jenis Kapal')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(JenisKapalImporter::class)
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/jenis_kapal_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),
                
            CreateAction::make()
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Jenis Kapal')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
