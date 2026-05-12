<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Resources\Dokumens\RelationManagers\HistoryDokumenRelationManager;
use App\Models\ReminderTemplate;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Enums\ContentTabPosition;

class EditDokumen extends EditRecord
{
    protected static string $resource = DokumenResource::class;
    protected ?bool $isRenew = false;
    public function mount($record): void
    {
        parent::mount($record);

        $this->isRenew = request()->boolean('renew');
    }
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Edit Dokumen';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['template_name']);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();

        if (!empty($data['save_as_template']) && !empty($data['template_name'])) {
            $template = ReminderTemplate::create([
                'nama_template' => $data['template_name'],
                'id_author'     => auth()->id(),
            ]);

            foreach ($this->record->reminderDokumen ?? [] as $reminder) {
                $template->reminderItems()->create([
                    'reminder_hari' => $reminder->reminder_hari,
                    'reminder_jam'  => $reminder->reminder_jam,
                ]);
            }

            foreach ($this->record->toReminderDokumen ?? [] as $to) {
                $template->toReminderItems()->create([
                    'nama'    => $to->nama,
                    'send_to' => $to->send_to,
                    'type'    => $to->type,
                ]);
            }
        }
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'historyDokumen.*.nomor_dokumen' => 'Nomor dokumen sudah digunakan.',
                ]);
            }

            throw $e;
        }
    }
}
