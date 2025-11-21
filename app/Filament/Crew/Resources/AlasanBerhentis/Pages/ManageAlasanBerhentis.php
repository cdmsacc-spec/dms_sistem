<?php

namespace App\Filament\Crew\Resources\AlasanBerhentis\Pages;

use App\Filament\Crew\Resources\AlasanBerhentis\AlasanBerhentiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;

class ManageAlasanBerhentis extends ManageRecords
{
    protected static string $resource = AlasanBerhentiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->color('info')
                ->label('New Data')
                ->icon('heroicon-o-pencil-square')
                ->modalIcon('heroicon-o-pencil-square')
                ->modalHeading('Add Alasan berhenti')
                ->modalWidth('xl')
                ->modalAlignment(Alignment::Center),
        ];
    }
}
