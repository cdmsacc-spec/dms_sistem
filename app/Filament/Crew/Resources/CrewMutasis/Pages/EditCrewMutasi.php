<?php

namespace App\Filament\Crew\Resources\CrewMutasis\Pages;

use App\Filament\Crew\Resources\CrewMutasis\CrewMutasiResource;
use App\Models\Lookup;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class EditCrewMutasi extends EditRecord
{
    protected static string $resource = CrewMutasiResource::class;

    protected static ?string $slug = 'mutasi-promosi';

    public function getBreadcrumb(): string
    {
        return 'Mutasi Promosi ' . $this->record->nama_crew;
    }
    public function getTitle(): string|Htmlable
    {
        return 'Mutasi / Promosi';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saved')
                ->label('Save')
                ->color('info')
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
            $lookup = Lookup::where('code', 'sign_on')->lockForUpdate()->first();
            if (
                !empty($item['kategory']) &&
                !empty($item['berangkat_dari']) &&
                !empty($item['id_perusahaan']) &&
                !empty($item['id_jabatan']) &&
                !empty($item['id_wilayah']) &&
                !empty($item['id_kapal']) &&
                !empty($item['gaji']) &&
                !empty($item['start_date']) &&
                !empty($item['end_date']) &&
                !empty($item['kontrak_lanjutan'])
            ) {
                $startDate = Carbon::parse($item['start_date']);


                $kontrak = $this->record->kontrak()->create([
                    'kategory' => $item['kategory'],
                    'nomor_dokumen'   => $lookup->name ?? 1,
                    'id_perusahaan'    => $item['id_perusahaan'],
                    'berangkat_dari'   => $item['berangkat_dari'],
                    'id_jabatan'       => $item['id_jabatan'],
                    'id_wilayah'       => $item['id_wilayah'],
                    'id_kapal'         => $item['id_kapal'],
                    'gaji'             => $item['gaji'],
                    'start_date'       => $startDate->format('Y-m-d'),
                    'end_date'         => Carbon::parse($item['end_date'])->format('Y-m-d'),
                    'kontrak_lanjutan' => $item['kontrak_lanjutan'],
                    'status_kontrak'   => 'waiting approval',
                    'file'        => null,
                ]);

                if ($kontrak) {
                    $lookup->name = $lookup->name + 1;
                    $lookup->save();
                    $this->form->fill();
                    $this->dispatch('refresh');

                    Notification::make()
                        ->title('Success')
                        ->body('The crew mutasi/promosi results have been successfully saved')
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
