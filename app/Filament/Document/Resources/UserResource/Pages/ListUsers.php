<?php

namespace App\Filament\Document\Resources\UserResource\Pages;

use App\Filament\Document\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Staff Document';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add New User'),
        ];
    }
}
