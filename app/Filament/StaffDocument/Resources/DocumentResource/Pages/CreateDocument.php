<?php

namespace App\Filament\StaffDocument\Resources\DocumentResource\Pages;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use App\Models\DocumentHistorie;
use App\Models\JenisDocument;
use App\Models\JenisKapal;
use App\Models\NamaKapal;
use App\Models\Perusahaan;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function afterCreate(): void
    {

        $state = $this->form->getRawState();

        if (!empty($state['tanggal_terbit']) && !empty($state['tanggal_expired']) && !empty($state['file_path'])) {
            DocumentExpiration::create([
                'document_id'     => $this->record->id,
                'tanggal_terbit'  => $state['tanggal_terbit'] ?? null,
                'tanggal_expired' => $state['tanggal_expired'] ?? null,
                'file_path'       => is_array($state['file_path'])
                    ? reset($state['file_path'])
                    : $state['file_path'],
            ]);
        }
    }
}
