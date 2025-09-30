<?php

namespace App\Filament\StaffCrew\Resources\CrewCandidateResource\Pages;

use App\Filament\StaffCrew\Resources\CrewCandidateResource;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\ActionSize;

class CreateCrewCandidates extends CreateRecord
{
    protected static string $resource = CrewCandidateResource::class;

    protected bool $isDraft = false;

    // Manipulasi data setelah validasi sukses
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status_proses'] = $this->isDraft ? 'Draft' : 'Ready For Interview';
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
