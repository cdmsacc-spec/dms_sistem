<?php

namespace App\Filament\Resources\Lookups\Pages;

use App\Filament\Imports\LookupImporter;
use App\Filament\Resources\Lookups\LookupResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageLookups extends ManageRecords
{
    protected static string $resource = LookupResource::class;

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make()
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Lookup')
                ->modalWidth('xl')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
