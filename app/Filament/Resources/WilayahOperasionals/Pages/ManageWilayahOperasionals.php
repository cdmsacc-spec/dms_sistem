<?php

namespace App\Filament\Resources\WilayahOperasionals\Pages;

use App\Filament\Imports\WilayahOperasionalImporter;
use App\Filament\Resources\WilayahOperasionals\WilayahOperasionalResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageWilayahOperasionals extends ManageRecords
{
    protected static string $resource = WilayahOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Wilayah Operasional')
                ->visible(fn() => auth()->user()->can('create:wilayah-operasional'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Wilayah Operasional')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(WilayahOperasionalImporter::class)
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/wilayah_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),

            CreateAction::make()
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Wilayah Operasional')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
