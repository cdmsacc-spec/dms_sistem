<?php

namespace App\Filament\Crew\Resources\UserResource\Pages;

use App\Filament\Crew\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

     public function getTitle(): string
    {
        return 'Update ' . $this->record->name;
    }

}
