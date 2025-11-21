<?php

namespace App\Filament\Resources\JenisDokumens\Pages;

use App\Filament\Imports\JenisDokumenImporter;
use App\Filament\Resources\JenisDokumens\JenisDokumenResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageJenisDokumens extends ManageRecords
{
    protected static string $resource = JenisDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Jenis Dokumen')
                ->visible(fn() => auth()->user()->can('create:jenis-dokumen'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Jenis Dokumen')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(JenisDokumenImporter::class)
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/jenis_dokumen_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),

            CreateAction::make()
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Jenis Dokumen')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
