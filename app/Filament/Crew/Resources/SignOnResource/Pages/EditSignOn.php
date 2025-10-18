<?php

namespace App\Filament\Crew\Resources\SignOnResource\Pages;

use App\Enums\StatusKontrakCrew;
use App\Filament\Crew\Resources\SignOnResource;
use App\Models\Lookup;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditSignOn extends EditRecord
{
    protected static string $resource = SignOnResource::class;

    public function getTitle(): string
    {
        return 'Sign on ' . $this->record->nama_crew;
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('saved')
                ->label('Save Change')
                ->color('primary')
                ->action(function (array $data) {
                    $this->afterSave();
                }),
            $this->getCancelFormAction()
        ];
    }

    protected function afterSave(): void
    {
        $item = $this->form->getRawState();

        DB::transaction(function () use ($item) {
            $lookup = Lookup::where('code', 'SignOn')->lockForUpdate()->first();
            if (
                !empty($lookup?->value) &&
                !empty($item['berangkat_dari']) &&
                !empty($item['perusahaan_id']) &&
                !empty($item['jabatan_id']) &&
                !empty($item['wilayah_id']) &&
                !empty($item['kapal_id']) &&
                !empty($item['gaji']) &&
                !empty($item['start_date']) &&
                !empty($item['end_date'])
            ) {
                $crewPkl = $this->record->crewPkl()->create([
                    'kategory' => 'Sign On',
                    'nomor_document'   => $lookup->value, // ambil current value
                    'perusahaan_id'    => $item['perusahaan_id'],
                    'berangkat_dari'   => $item['berangkat_dari'],
                    'jabatan_id'       => $item['jabatan_id'],
                    'wilayah_id'       => $item['wilayah_id'],
                    'kapal_id'         => $item['kapal_id'],
                    'gaji'             => $item['gaji'],
                    'start_date'       => \Carbon\Carbon::parse($item['start_date'])->format('Y-m-d'),
                    'end_date'         => \Carbon\Carbon::parse($item['end_date'])->format('Y-m-d'),
                    'kontrak_lanjutan' => false,
                    'status_kontrak'   => StatusKontrakCrew::WaitingApproval,
                    'file_path'        => null,
                ]);

                if ($crewPkl) {
                    $lookup->value = $lookup->value + 1;
                    $lookup->save();
                    $this->form->fill();
                    $this->dispatch('refresh');

                    Notification::make()
                        ->title('Success')
                        ->body('The crew Sign On results have been successfully saved')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Failed')
                        ->danger()
                        ->body('An error occurred while saving the internship data.')
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('Incomplete Data!')
                    ->body('Please make sure all required fields are filled in before saving.')
                    ->danger()
                    ->send();
            }
        });
    }
}
