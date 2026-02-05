<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Resources\Dokumens\RelationManagers\HistoryDokumenRelationManager;
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
