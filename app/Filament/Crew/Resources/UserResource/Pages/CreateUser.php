<?php

namespace App\Filament\Crew\Resources\UserResource\Pages;

use App\Filament\Crew\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
        protected static ?string $title = 'Create New Staff Crew';

    public function afterCreate(): void
    {
        $user = $this->record;
        $user->assignRole('staff_crew');
    }
}
