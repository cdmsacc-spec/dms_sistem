<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Resources\Dokumens\RelationManagers\HistoryDokumenRelationManager;
use App\Models\ReminderTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class CreateDokumen extends CreateRecord
{
    protected static string $resource = DokumenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_author'] = auth()->user()->id;
        $data['status'] = 'uptodate';

        unset($data['template_name']);

        return $data;
    }
    public static function getRelations(): array
    {
        return [
            HistoryDokumenRelationManager::class,
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'historyDokumen.*.nomor_dokumen' => 'Nomor dokumen sudah digunakan.',
                ]);
            }

            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        if ($data['save_as_template'] && !empty($data['template_name'])){
            $saveAsTemplate = (bool) ($data['save_as_template'] ?? false);
            $templateName   = $data['template_name'] ?? null;

            if (!$saveAsTemplate || empty($templateName)) {
                return;
            }

            // Ambil data reminder dari relasi yang sudah disimpan oleh Filament
            $record = $this->record;

            $template = ReminderTemplate::create([
                'nama_template' => $templateName,
                'id_author'     => auth()->id(),
            ]);

            foreach ($record->reminderDokumen ?? [] as $reminder) {
                $template->reminderItems()->create([
                    'reminder_hari' => $reminder->reminder_hari,
                    'reminder_jam'  => $reminder->reminder_jam,
                ]);
            }

            foreach ($record->toReminderDokumen ?? [] as $to) {
                $template->toReminderItems()->create([
                    'nama'    => $to->nama,
                    'send_to' => $to->send_to,
                    'type'    => $to->type,
                ]);
            }
        }
    }
}
