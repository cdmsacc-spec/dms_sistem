<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateAllCrew extends CreateRecord
{
    protected static string $resource = AllCrewResource::class;
    protected bool $isDraft = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $this->isDraft ? 'draft' : 'ready for interview';
        return $data;
    }

     protected function getFormActions(): array
    {
        return [
           Action::make('submit')
                ->label('Submit')
                ->color('info')
                ->action(function (array $data) {
                    $this->isDraft = false;
                    $this->create();
                }),

            Action::make('draft')
                ->label('Save As Draft')
                ->color('warning')
                ->action(function (array $data) {
                    $this->isDraft = true;
                    $this->create();
                }),
        ];
    }
}
