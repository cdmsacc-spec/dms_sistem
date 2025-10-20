<?php

namespace App\Filament\Crew\Resources\AppraisalResource\Pages;

use App\Filament\Crew\Resources\AppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisals extends ListRecords
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
