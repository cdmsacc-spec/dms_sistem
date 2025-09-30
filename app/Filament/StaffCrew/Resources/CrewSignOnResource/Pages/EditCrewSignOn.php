<?php

namespace App\Filament\StaffCrew\Resources\CrewSignOnResource\Pages;

use App\Filament\StaffCrew\Resources\CrewSignOnResource;
use App\Models\Lookup;
use App\Models\Perusahaan;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCrewSignOn extends EditRecord
{
    protected static string $resource = CrewSignOnResource::class;
    public function getTitle(): string
    {
        return 'Sign on ' . $this->record->nama_crew;
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
                !empty($item['end_date']) &&
                !empty($item['status_kontrak'])
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
                    'status_kontrak'   => $item['status_kontrak'],
                    'file_path'        => null,
                ]);

                // kalau create berhasil, increment lookup
                if ($crewPkl) {
                    $lookup->value = $lookup->value + 1;
                    $lookup->save();
                    $this->form->fill(); // reset input form
                    $this->dispatch('refresh');

                    // Notification::make()
                    //     ->title('Saved successfully')
                    //     ->success()
                    //     ->body('Data PKL berhasil ditambahkan')
                    //     ->actions([
                    //         Action::make('generate_form')
                    //             ->button()
                    //             ->url(route('generate.signon', [
                    //                 'id' => $crewPkl->id,
                    //              ]), shouldOpenInNewTab: true),
                    //       ])
                    //     ->send();
                } else {
                    Notification::make()
                        ->title('Gagal menyimpan')
                        ->danger()
                        ->body('Terjadi kesalahan saat menyimpan data PKL')
                        ->send();
                }
            }
        });
    }
}
