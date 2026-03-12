<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Crew;
use App\Models\CrewDokumen;
use App\Models\CrewKontrak;
use App\Models\CrewSertifikat;
use App\Models\Lookup;
use App\Models\ReminderCrew;
use App\Models\ToReminderCrew;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CrewNotidWaService
{

    protected $groupedNotifications = [];
    public function updateAll()
    {
        Crew::with([
            'dokumen' => fn($q) => $q->whereNotNull('tanggal_expired'),
            'sertifikat' => fn($q) => $q->whereNotNull('tanggal_expired'),
            'lastKontrak'
        ])->chunk(100, function ($dokumen) {
            $lookups = Lookup::where('type', 'reminder_time_wa')
                ->whereIn('code', ['reminder_time_wa_crew'])
                ->orderBy('id')
                ->pluck('name', 'code');
            $today = Carbon::now(config('app.timezone'))->startOfDay();
            if ($today->format('H:i') != $lookups['reminder_time_wa_crew'] ?? '00:00') {
                // return;
            }
            foreach ($dokumen as $data) {
                $this->reminderWa($data, $today);
            }
        });

        $this->sendAggregatedWa();
    }

    public function reminderWa(Crew $data, $today)
    {
        $reminder = ReminderCrew::all();
        $toReminder =  ToReminderCrew::where('type', 'wa')->get();

        if (!empty($data->dokumen)) {
            foreach ($data->dokumen  as $doc) {
                $expiredDate = Carbon::parse($doc->tanggal_expired)->startOfDay();
                foreach ($reminder as $remind) {
                    $daysArray = is_array($remind->reminder_hari)
                        ? $remind->reminder_hari
                        : explode(',', $remind->reminder_hari);

                    foreach ($daysArray as $day) {

                        $targetDate = $expiredDate->copy()->subDays((int)$day);
                        if ($today->equalTo($targetDate)) {
                            foreach ($toReminder as $recipient) {

                                if ($recipient->type == 'wa') {
                                    $wa = $recipient->send_to;
                                    if (!$wa) continue;
                                    // GROUPING PER NOMOR WAnomor_dokumen
                                    $this->groupedNotifications[$wa]['nama'] = $recipient->nama;
                                    $this->groupedNotifications[$wa]['no_doc'][] = 'Crew ' . $data->nama_crew . ' Dokumen ' . $doc->jenis_dokumen . ' - Expired: ' . Carbon::parse($doc->tanggal_expired)->format('d M Y');
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($data->sertifikat)) {
            foreach ($data->sertifikat  as $doc) {
                $expiredDate = Carbon::parse($doc->tanggal_expired)->startOfDay();
                foreach ($reminder as $remind) {
                    $daysArray = is_array($remind->reminder_hari)
                        ? $remind->reminder_hari
                        : explode(',', $remind->reminder_hari);

                    foreach ($daysArray as $day) {

                        $targetDate = $expiredDate->copy()->subDays((int)$day);
                        if ($today->equalTo($targetDate)) {
                            foreach ($toReminder as $recipient) {

                                if ($recipient->type == 'wa') {
                                    $wa = $recipient->send_to;
                                    if (!$wa) continue;
                                    // GROUPING PER NOMOR WAnomor_dokumen
                                    $this->groupedNotifications[$wa]['nama'] = $recipient->nama;
                                    $this->groupedNotifications[$wa]['no_doc'][] = 'Crew ' . $data->nama_crew . ' Sertifikat ' . $doc->nama_sertifikat . ' - Expired: ' . Carbon::parse($doc->tanggal_expired)->format('d M Y');
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($data->lastKontrak)) {
            $expiredDate = Carbon::parse($data->lastKontrak->end_date)->startOfDay();
            foreach ($reminder as $remind) {
                $daysArray = is_array($remind->reminder_hari)
                    ? $remind->reminder_hari
                    : explode(',', $remind->reminder_hari);

                foreach ($daysArray as $day) {

                    $targetDate = $expiredDate->copy()->subDays((int)$day);
                    if ($today->equalTo($targetDate)) {
                        foreach ($toReminder as $recipient) {

                            if ($recipient->type == 'wa') {
                                $wa = $recipient->send_to;
                                if (!$wa) continue;
                                // GROUPING PER NOMOR WAnomor_dokumen
                                $this->groupedNotifications[$wa]['nama'] = $recipient->nama;
                                $this->groupedNotifications[$wa]['no_doc'][] = 'Crew ' . $data->nama_crew . ' Kontrak Nomor ' . $data->lastKontrak->nomor_dokumen . ' - Expired: ' . Carbon::parse($data->lastKontrak->end_date)->format('d M Y');
                            }
                        }
                    }
                }
            }
        }
    }

    private function sendAggregatedWa()
    {

        $lookups = Lookup::where('type', 'reminder_time_wa')
            ->whereIn('code', ['reminder_time_wa_crew'])
            ->orderBy('id')
            ->pluck('name', 'code');

        $today = Carbon::now(config('app.timezone'));
        if ($today->format('H:i') === $lookups['reminder_time_wa_crew'] ?? '00:00') {
            if (empty($this->groupedNotifications)) return;
            foreach ($this->groupedNotifications as $wa => $info) {
                $listNoDoc = implode(", ", $info['no_doc']);
                $variables = [
                    ['key' => '1', 'value' => 'nama', 'value_text' => $info['nama']],
                    ['key' => '2', 'value' => 'no_doc', 'value_text' => $listNoDoc],
                    ['key' => '3', 'value' => 'url', 'value_text' => url("/crew")],
                ];
                try {
                    SendWhatsAppNotificationJob::dispatchSync($wa, $info['nama'], $variables);
                    Log::info($info['nama'], $variables);
                } catch (\Throwable $th) {
                    Log::error("Gagal dispatch WA ke $wa: " . $th->getMessage());
                }
            }
        }
    }
}
