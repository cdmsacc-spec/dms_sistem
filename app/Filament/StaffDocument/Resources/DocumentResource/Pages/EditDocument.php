<?php

namespace App\Filament\StaffDocument\Resources\DocumentResource\Pages;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use App\Models\DocumentHistorie;
use App\Models\DocumentReminder;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;

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
        $state = $this->form->getRawState();
        $original = DocumentExpiration::where('document_id', $this->record->id)
            ->latest()
            ->first();

        // Ambil state baru
        $newTanggalTerbit  = $state['tanggal_terbit'] ?? null;
        $newTanggalExpired = $state['tanggal_expired'] ?? null;
        $newFilePath       = is_array($state['file_path']) ? reset($state['file_path']) : $state['file_path'];

        // Cek apakah ada perubahan dibanding original
        $isDifferent = !$original ||
            $newTanggalTerbit !== $original->tanggal_terbit ||
            $newTanggalExpired !== $original->tanggal_expired;

        if (!empty($newTanggalTerbit) && $isDifferent) {
            if (!empty($newFilePath)) {
                $this->record->status = 'UpToDate';
                $this->record->save();
                DocumentExpiration::create([
                    'document_id'     => $this->record->id,
                    'tanggal_terbit'  => $newTanggalTerbit,
                    'tanggal_expired' => $newTanggalExpired,
                    'file_path'       => $newFilePath,
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
