<?php

namespace App\Filament\Resources\Kapals\Pages;

use App\Filament\Imports\KapalImporter;
use App\Filament\Resources\Kapals\KapalResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageKapals extends ManageRecords
{
    protected static string $resource = KapalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Kapal')
                ->visible(fn() => auth()->user()->can('create:kapal'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Kapal')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan. Perhatikan jenis kapal, wilayah dan perusahaan pastikan nama perusahaan, wilayah dan jenis kapal yang dimasukan sudah tersedia di database')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(KapalImporter::class)
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/kapal_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),
            CreateAction::make()
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Kapal')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
