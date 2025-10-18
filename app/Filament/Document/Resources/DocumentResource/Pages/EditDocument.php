<?php

namespace App\Filament\Document\Resources\DocumentResource\Pages;

use App\Filament\Document\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

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

    protected function afterSave(): void
    {
        try {
            $state = $this->form->getRawState();
            $newFilePath = is_array($state['file_path']) ? reset($state['file_path']) : $state['file_path'];

            if (!empty($newFilePath)) {
                // Jika ada file baru → buat record baru
                DocumentExpiration::create([
                    'document_id'     => $this->record->id,
                    'tanggal_terbit'  => $state['tanggal_terbit'],
                    'tanggal_expired' => $state['tanggal_expired'],
                    'file_path'       => $newFilePath,
                ]);
            } else {
                // Jika tidak ada file baru → update data yang sudah ada
                if (!empty($state['tanggal_terbit']) || !empty($state['tanggal_expired'])) {
                    $this->record->latestExpiration->update([
                        'tanggal_terbit'  => $state['tanggal_terbit'],
                        'tanggal_expired' => $state['tanggal_expired'],
                    ]);
                }
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan saat menyimpan data: ' . $th->getMessage())
                ->danger()
                ->send();

            throw $th;
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
