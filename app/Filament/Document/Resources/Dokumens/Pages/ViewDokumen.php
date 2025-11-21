<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDokumen extends ViewRecord
{
    protected static string $resource = DokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('audit')
                ->button()->color('info')
                ->label('Dokumen Audit')->url(fn($record) => DokumenAudit::getUrl(['record' => $record])),
            EditAction::make(),
        ];
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Dokumen ' . $this->record->jenisDokumen->nama_jenis;
    }
}
