<?php

namespace App\Filament\Crew\Resources\SignOffResource\Pages;

use App\Enums\StatusKontrakCrew;
use App\Filament\Crew\Resources\SignOffResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditSignOff extends EditRecord
{
    protected static string $resource = SignOffResource::class;
    public ?string $tanggalSignOff = null;

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}

    public function getTitle(): string
    {
        return 'Sign Off ' . $this->record->nama_crew;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate Document Sign Off')
                ->color('info')
                ->icon('heroicon-o-printer')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-printer')
                ->modalDescription('')
                ->form([
                    DatePicker::make('tanggal')
                        ->label('Tanggal Sign Off')
                        ->prefixIcon('heroicon-m-calendar')
                        ->native(false)
                        ->required(),
                    Textarea::make('alasan_berhenti')
                        ->label('Alasan Berhenti')
                        ->required(),
                ])
                ->modalWidth(MaxWidth::Small)
                ->action(function (array $data,  $action) {
                    $alasan_berhenti = $data['alasan_berhenti'];
                    $this->tanggalSignOff = $data['tanggal'];
                    return redirect()->route('generate.signoff', [
                        'id' => $this->record->id,
                        'alasan_berhenti' => $alasan_berhenti,
                        'tanggal_signoff' => $this->tanggalSignOff

                    ]);
                }),
        ];
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('saved')
                ->label('Save Change')
                ->color('primary')
                ->action(function (array $data) {
                    $item = $this->form->getRawState();
                    if (!empty($this->tanggalSignOff) && !empty($item['file_path'])) {
                        $this->afterSave();
                    } else {
                        Notification::make()
                            ->title('Incomplete Data!')
                            ->body('Please make sure all required fields are filled in before saving.')
                            ->danger()
                            ->send();
                    }
                }),
            $this->getCancelFormAction()
        ];
    }

    protected function afterSave(): void
    {
        try {
            $item = $this->form->getRawState();
            $file = is_array($item['file_path']) ? reset($item['file_path']) : $item['file_path'];
            $this->record->crewPkl()
                ->where('status_kontrak', 'Active')
                ->update(['status_kontrak' => StatusKontrakCrew::Expired, 'end_date' =>  $this->tanggalSignOff]);
            $this->record->update(['status_proses' => 'Inactive']);
            $this->record->crewSignOff()->create([
                'tanggal' =>  $this->tanggalSignOff ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                'file_path'  => $file
            ]);
            $this->form->fill();
            $this->dispatch('refresh');
            Notification::make()
                ->title('Success')
                ->body('The crew Sign Off results have been successfully saved')
                ->success()
                ->send();
            redirect($this->getResource()::getUrl('index'));
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Failed')
                ->danger()
                ->body('An error occurred while saving the internship data.')
                ->send();
        }
    }
}
