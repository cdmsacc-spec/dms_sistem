<?php

namespace App\Filament\Document\Resources\DocumentResource\Pages;

use App\Filament\Document\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;
    protected function beforeCreate(): void
    {
        $state = $this->form->getRawState();
        $newTanggalTerbit  = $state['tanggal_terbit'] ?? null;
        $newFilePath       = is_array($state['file_path']) ? reset($state['file_path']) : $state['file_path'];

        if (empty($newTanggalTerbit) || empty($newFilePath)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->body('File dan Tanggal Terbit wajib diisi!')
                ->danger()
                ->send();

            throw new Halt();
        }
    }
    protected function afterCreate(): void
    {
        $state = $this->form->getRawState();
        if (!empty($state['tanggal_terbit'])  && !empty($state['file_path'])) {
            DocumentExpiration::create([
                'document_id'     => $this->record->id,
                'tanggal_terbit'  => $state['tanggal_terbit'],
                'tanggal_expired' => $state['tanggal_expired'] ?? null,
                'file_path'       => is_array($state['file_path'])
                    ? reset($state['file_path'])
                    : $state['file_path'],
            ]);
        }
    }
}
