<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Filament\Imports\CrewImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListAllCrews extends ListRecords
{
    protected static string $resource = AllCrewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->label('Import Crew')
                ->visible(fn() => auth()->user()->can('create:crew'))
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Import Data Crew')
                ->modalDescription('pastikan file csv anda valid dengan data example csv, perhatikan penulisan huruf besar kecil dan kesesuaian data yang sudah ditetapkan')
                ->modalWidth('md')
                ->color('warning')
                ->modalIcon('heroicon-o-arrow-up-on-square-stack')
                ->importer(CrewImporter::class)
                ->options([
                    'status' => 'draft'
                ])
                ->extraModalFooterActions([
                    Action::make('download-example-csv')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(
                            fn() =>
                            asset('storage/templates/crew_sample.csv'),
                            shouldOpenInNewTab: true
                        ),
                ]),

            CreateAction::make()
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
