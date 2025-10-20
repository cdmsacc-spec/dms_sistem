<?php

namespace App\Filament\Crew\Resources\SignOffResource\Pages;

use App\Filament\Crew\Resources\SignOffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignOffs extends ListRecords
{
    protected static string $resource = SignOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
