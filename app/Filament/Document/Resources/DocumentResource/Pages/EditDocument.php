<?php

namespace App\Filament\Document\Resources\DocumentResource\Pages;

use App\Filament\Document\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;
    protected static ?string $title = 'Renew Document';

    protected function getHeaderActions(): array
    {
        return [];
    }
    protected ?bool $isRenew = false;

    public function mount($record): void
    {
        parent::mount($record);

        $this->isRenew = request()->boolean('renew');
    }

    protected function beforeSave(): void
    {

        $state = $this->form->getRawState();
        $newFilePath = is_array($state['file_path']) ? reset($state['file_path']) : $state['file_path'];

        if (!empty($newFilePath)) {
            // Jika ada file baru → buat record baru
            if (!empty($state['nomor_dokumen'])) {
                DocumentExpiration::create([
                    'document_id'     => $this->record->id,
                    'nomor_dokumen'  => $state['nomor_dokumen'],
                    'tanggal_terbit'  => $state['tanggal_terbit'],
                    'tanggal_expired' => $state['tanggal_expired'],
                    'file_path'       => $newFilePath,
                ]);
            } else {
                Notification::make()
                    ->title('Field Requeired')
                    ->body('Saat memperbarui file dokumen, nomor dokumen tidak boleh kosong')
                    ->danger()
                    ->send();
                throw new Halt();
            }
        } else {
            // Jika tidak ada file baru → update data yang sudah ada
            if (!empty($state['tanggal_terbit']) || !empty($state['tanggal_expired']) || !empty($state['nomor_dokumen'])) {
                $this->record->latestExpiration->update([
                    'nomor_dokumen'  => $state['nomor_dokumen'] ?? $this->record->latestExpiration->nomor_dokumen,
                    'tanggal_terbit'  => $state['tanggal_terbit'] ?? $this->record->latestExpiration->tanggal_terbit,
                    'tanggal_expired' => $state['tanggal_expired'],
                ]);
            }
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            DocumentResource::getUrl('index') => 'Document',
            ViewDocument::getUrl(['record' => $this->record]) => 'View',
            null => 'Edit',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ViewDocument::getUrl(['record' => $this->record]);
    }
}
