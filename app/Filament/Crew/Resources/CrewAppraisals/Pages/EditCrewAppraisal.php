<?php

namespace App\Filament\Crew\Resources\CrewAppraisals\Pages;

use App\Filament\Crew\Resources\CrewAppraisals\CrewAppraisalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCrewAppraisal extends EditRecord
{
    protected static string $resource = CrewAppraisalResource::class;
    protected static ?string $slug = 'appraisal';

    public function getBreadcrumb(): string
    {
        return 'Appraisal ' . $this->record->crew->nama_crew;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Appraisal';
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
                !empty($item['aprraiser']) &&
                !empty($item['nilai']) &&
                !empty($item['keterangan'])
            ) {
                $this->record->appraisal()->create([
                    'id_kontrak' => $item['id'],
                    'aprraiser' => $item['aprraiser'],
                    'nilai' => $item['nilai'],
                    'keterangan' => $item['keterangan'],
                ]);
                $this->dispatch('refresh');
            } else {
                Notification::make()
                    ->title('Incomplete Data!')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Failed')
                ->danger()
                ->body($th->getMessage())
                ->send();
        }
    }
}
