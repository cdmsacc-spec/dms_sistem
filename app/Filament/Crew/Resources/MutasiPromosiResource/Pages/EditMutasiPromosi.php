<?php

namespace App\Filament\Crew\Resources\MutasiPromosiResource\Pages;

use App\Enums\StatusKontrakCrew;
use App\Filament\Crew\Resources\MutasiPromosiResource;
use App\Models\Lookup;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditMutasiPromosi extends EditRecord
{
    protected static string $resource = MutasiPromosiResource::class;

    public function getTitle(): string
    {
        return 'Promosi ' . $this->record->nama_crew;
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
        try {
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
                    !empty($item['end_date']) &&
                    !empty($item['kontrak_lanjutan'])
                ) {
                    $startDate = \Carbon\Carbon::parse($item['start_date']);

                    if ($item['kontrak_lanjutan'] == 1) {
                        $oldContract = $this->record->crewPkl()
                            ->latest('end_date')
                            ->first();
                        if ($oldContract) {
                            $oldContract->update([
                                'end_date' => $startDate->copy()->subDay()->format('Y-m-d'),
                            ]);
                        }
                    }
                    $crewPkl = $this->record->crewPkl()->create([
                        'kategory' => 'Promosi',
                        'nomor_document'   => $lookup->value, 
                        'perusahaan_id'    => $item['perusahaan_id'],
                        'berangkat_dari'   => $item['berangkat_dari'],
                        'jabatan_id'       => $item['jabatan_id'],
                        'wilayah_id'       => $item['wilayah_id'],
                        'kapal_id'         => $item['kapal_id'],
                        'gaji'             => $item['gaji'],
                        'start_date'       => $startDate->format('Y-m-d'),
                        'end_date'         => $item['end_date'],
                        'kontrak_lanjutan' => $item['kontrak_lanjutan'],
                        'status_kontrak'   => StatusKontrakCrew::WaitingApproval,
                        'file_path'        => null,
                    ]);

                    // kalau create berhasil, increment lookup
                    if ($crewPkl) {
                        $lookup->value = $lookup->value + 1;
                        $lookup->save();
                        $this->form->fill(); // reset input form
                        $this->dispatch('refresh');
                        Notification::make()
                            ->title('Success')
                            ->body('The crew Mutasi/Promosi results have been successfully saved')
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
        } catch (\Throwable $th) {
            Log::error('Terjadi error: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
