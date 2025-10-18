<?php

namespace App\Filament\Crew\Resources\AppraisalResource\Pages;

use App\Filament\Crew\Resources\AppraisalResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    public function getTitle(): string
    {
        return 'Appraisal ' . $this->record->crew->nama_crew;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Add Data')
                ->color('primary')
                ->icon('heroicon-o-check-circle'),
        ];
    }

    protected function afterSave()
    {

        try {
            $item = $this->form->getRawState();
            if (
                !empty($item['appraiser']) &&
                !empty($item['nilai']) &&
                !empty($item['keterangan'])
            ) {
                $this->record->appraisal()->create([
                    'pkl_id' => $item['id'],
                    'appraiser' => $item['appraiser'],
                    'nilai' => $item['nilai'],
                    'keterangan' => $item['keterangan'],
                ]);
                $this->dispatch('refresh');
            } else {
                Notification::make()
                    ->title('Incomplete Data!')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Failed')
                ->danger()
                ->body('An error occurred while saving the internship data.')
                ->send();
        }
    }
}
