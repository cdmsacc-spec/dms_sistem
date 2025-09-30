<?php

namespace App\Filament\StaffCrew\Resources\CrewPromosiResource\Pages;

use App\Filament\StaffCrew\Resources\CrewOverviewResource;
use App\Filament\StaffCrew\Resources\CrewPromosiResource;
use Filament\Actions;
use App\Models\Lookup;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditCrewPromosi extends EditRecord
{
    protected static string $resource = CrewPromosiResource::class;
    public function getTitle(): string
    {
        return 'Promosi ' . $this->record->nama_crew;
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
                    !empty($item['kontrak_lanjutan']) &&
                    !empty($item['status_kontrak'])
                ) {
                    $startDate = \Carbon\Carbon::parse($item['start_date']);

                    if ($item['kontrak_lanjutan'] == 1) {
                        $oldContract = $this->record->crewPkl()
                            ->latest('end_date')
                            ->first();
                        if ($oldContract) {
                            // update kontrak lama end_date = H-1 dari start kontrak baru
                            $oldContract->update([
                                'end_date' => $startDate->copy()->subDay()->format('Y-m-d'),
                            ]);

                            // end_date kontrak baru = end_date kontrak lama (sebelum diupdate)
                            $item['end_date'] = $startDate->copy()->addMonths(9)->format('Y-m-d');
                        }
                    }
                    $crewPkl = $this->record->crewPkl()->create([
                        'kategory' => 'Promosi',
                        'nomor_document'   => $lookup->value, // ambil current value
                        'perusahaan_id'    => $item['perusahaan_id'],
                        'berangkat_dari'   => $item['berangkat_dari'],
                        'jabatan_id'       => $item['jabatan_id'],
                        'wilayah_id'       => $item['wilayah_id'],
                        'kapal_id'         => $item['kapal_id'],
                        'gaji'             => $item['gaji'],
                        'start_date'       => $startDate->format('Y-m-d'),
                        'end_date'         => $item['end_date'],
                        'kontrak_lanjutan' => $item['kontrak_lanjutan'],
                        'status_kontrak'   => $item['status_kontrak'],
                        'file_path'        => null,
                    ]);

                    // kalau create berhasil, increment lookup
                    if ($crewPkl) {
                        $lookup->value = $lookup->value + 1;
                        $lookup->save();
                        $this->form->fill(); // reset input form
                        $this->dispatch('refresh');

                    } else {
                        Notification::make()
                            ->title('Gagal menyimpan')
                            ->danger()
                            ->body('Terjadi kesalahan saat menyimpan data PKL')
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Gagal menyimpan')
                        ->danger()
                        ->body('Harap Lengkapi Form Untuk Melanjutkan')
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
