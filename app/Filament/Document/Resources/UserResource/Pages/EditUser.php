<?php

namespace App\Filament\Document\Resources\UserResource\Pages;

use App\Filament\Document\Resources\UserResource;
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
