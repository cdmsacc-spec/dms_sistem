<?php

namespace App\Filament\StaffCrew\Resources\CrewPromosiResource\Pages;

use App\Filament\StaffCrew\Resources\CrewPromosiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrewPromosis extends ListRecords
{
    protected static string $resource = CrewPromosiResource::class;

    protected static ?string $title = 'Mutasi Promosi';
}
