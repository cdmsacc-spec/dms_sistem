<?php

namespace App\Filament\Crew\Resources\UserResource\Pages;

use App\Filament\Crew\Resources\UserResource;
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
