<?php

namespace App\Filament\Resources\Perusahaans\Pages;

use App\Filament\Imports\PerusahaanImporter;
use App\Filament\Resources\Perusahaans\PerusahaanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManagePerusahaans extends ManageRecords
{
    protected static string $resource = PerusahaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Perusahaan')
                ->visible(fn() => auth()->user()->can('create:perusahaan'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Perusahaan')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(PerusahaanImporter::class)
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/perusahaan_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),

            CreateAction::make()
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Perusahaan')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
