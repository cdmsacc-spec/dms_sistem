<?php

namespace App\Filament\Document\Resources\UserResource\Pages;

use App\Filament\Document\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
        protected static ?string $title = 'Create New Staff Document';

    public function afterCreate(): void
    {
        $user = $this->record;
        $user->assignRole('staff_document');
    }
}
