<?php

namespace App\Filament\StaffCrew\Resources\CrewPklResource\Pages;

use App\Filament\StaffCrew\Resources\CrewPklResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCrewPkl extends EditRecord
{
    protected static string $resource = CrewPklResource::class;

    protected bool $refreshRelationsWhenSaved = true;

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
                    ->title('Gagal menyimpan')
                    ->danger()
                    ->body('Harap Lengkapi Form Untuk Melanjutkan')
                    ->send();
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->danger()
                ->body('Terjadi kesalahan jaringan')
                ->send();
        }
    }
}
