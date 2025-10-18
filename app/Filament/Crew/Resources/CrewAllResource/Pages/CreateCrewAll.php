<?php

namespace App\Filament\Crew\Resources\CrewAllResource\Pages;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\CrewAllResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCrewAll extends CreateRecord
{
    protected static string $resource = CrewAllResource::class;
    protected bool $isDraft = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status_proses'] = $this->isDraft ? StatusCrew::Draft : StatusCrew::ReadyForInterview;
        return $data;
    }
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Submit')
                ->color('primary')
                ->action(function (array $data) {
                    $this->isDraft = false;
                    $this->create();
                }),

            Actions\Action::make('draft')
                ->label('Save As Draft')
                ->color('warning')
                ->action(function (array $data) {
                    $this->isDraft = true;
                    $this->create();
                }),
        ];
    }
}
