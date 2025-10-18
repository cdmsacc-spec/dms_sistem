<?php

namespace App\Filament\Crew\Resources\PerusahaanResource\Pages;

use App\Filament\Crew\Resources\PerusahaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePerusahaans extends ManageRecords
{
    protected static string $resource = PerusahaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
