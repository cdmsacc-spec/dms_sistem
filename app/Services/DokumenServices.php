<?php

namespace App\Services;

use App\Jobs\SendFcmNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Mail\MailServices;
use App\Models\Dokumen;
use App\Models\Lookup;
use App\Models\ToReminderDokumen;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DokumenServices
{

    protected $notifGroup = [];
    public function updateAll(): void
    {
        $lookups = Lookup::where('type', 'reminder_time_wa')
            ->whereIn('code', ['reminder_time_wa_doc'])
            ->orderBy('id')
            ->pluck('name', 'code');
        $today = Carbon::now(config('app.timezone'));

        Dokumen::with(['historyDokumen' => fn($q) => $q->latest(), 'reminderDokumen', 'kapal', 'toReminderDokumen'])
            ->chunk(100, function ($dokumen) use ($today, $lookups) {
                foreach ($dokumen as $data) {
                    $this->updateStatus($data);
                    if ($today->format('H:i') === $lookups['reminder_time_wa_doc'] ?? '00:00') {
                        $this->reminderWa($data);
                    }
                }
            });

        $this->sendAggregatedWa();
    }

    public function reminderWa(Dokumen $data)
    {
        try {
            $today = Carbon::now(config('app.timezone'));
            $expiration = $data->historyDokumen->first();
            if (!$expiration || !$expiration->tanggal_expired) return;
            $expiredDate = Carbon::parse($expiration->tanggal_expired)->startOfDay();
            $today = Carbon::now(config('app.timezone'))->startOfDay();

            foreach ($data->reminderDokumen as $reminder) {
                $daysArray = is_array($reminder->reminder_hari)
                    ? $reminder->reminder_hari
                    : explode(',', $reminder->reminder_hari);

                foreach ($daysArray as $day) {
                    // Cek H-Day (Expired - Hari Pengingat)
                    $targetDate = $expiredDate->copy()->subDays((int)$day);

                    // Validasi: Jika hari ini adalah jadwalnya (tanpa cek jam)
                    if ($today->equalTo($targetDate)) {
                        foreach ($data->toReminderDokumen as $recipient) {
                            if ($recipient->type == 'wa') {
                                $wa = $recipient->send_to;
                                if (!$wa) continue;

                                // GROUPING PER NOMOR WAnomor_dokumen
                                $this->notifGroup[$wa]['nama'] = $recipient->nama;
                                $this->notifGroup[$wa]['no_doc'][] = $data->jenisDokumen->nama_jenis . ' - ' . $data->kapal->nama_kapal . ' - Expired: ' . Carbon::parse($expiration->tanggal_expired)->format('d M Y');
                                // $this->notifGroup[$wa]['tgl_exp'][] = $expiration->tanggal_expired;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::info("fungsi reminderWa error");
            Log::error("Error reminderWa: " . $th->getMessage());
        }
    }

    private function sendAggregatedWa()
    {
        if (empty($this->notifGroup)) return;
        foreach ($this->notifGroup as $wa => $info) {
            $listNoDoc = implode(", ", $info['no_doc']);
            $variables = [
                ['key' => '1', 'value' => 'nama', 'value_text' => $info['nama']],
                ['key' => '2', 'value' => 'no_doc', 'value_text' => $listNoDoc],
                ['key' => '3', 'value' => 'url', 'value_text' => url("/document")],
            ];
            try {
               //  SendWhatsAppNotificationJob::dispatchSync($wa, $info['nama'], $variables);
                Log::info($info['nama'], $variables);
            } catch (\Throwable $th) {
                Log::error(message: "Gagal dispatch WA ke $wa: " . $th->getMessage());
            }
        }
    }

    public function updateStatus(Dokumen $data)
    {
        try {
            $today = Carbon::now(config('app.timezone'));
            $expiration = $data->historyDokumen->first();

            $uptodate = 'uptodate';
            $nearExpiry = 'near expiry';
            $expired = 'expired';

            $rectangleDate = 30;

            if (!$expiration || !$expiration->tanggal_expired) {
                if ($data->status !== $uptodate) {
                    $data->status = $uptodate;
                    $data->save();
                    $this->sendNotification($data,  $uptodate, $today);
                }
                return;
            }

            $expiredDate = Carbon::parse($expiration->tanggal_expired, config('app.timezone'))->endOfDay();
            $nearExpiryDate = $expiredDate->copy()->subDays((int) $rectangleDate);

            // Log::info('=== DEBUG EXPIRATION CHECK ===', [
            //     'Today' => $today->toDateTimeString(),
            //     'Expired Date' => $expiredDate->toDateTimeString(),
            //     'Near Expiry Date' => $nearExpiryDate->toDateTimeString(),
            //     'Greater/Eq to NearExpiry?' => $today->greaterThanOrEqualTo($nearExpiryDate),
            //     'Less than Expired?' => $today->lessThan($expiredDate),
            //     'Status Sekarang' => $data->status ?? null,
            // ]);

            if ($today->greaterThanOrEqualTo($nearExpiryDate) && $today->lessThan($expiredDate)) {
                //  Log::info('✅ MASUK RENTANG 30 HARI');
                if ($data->status !== $nearExpiry) {
                    $data->status = $nearExpiry;
                    $data->save();
                    $this->sendNotification($data,  $nearExpiry, $today);
                }
            } else if ($today->lessThan($nearExpiryDate)) {
                if ($data->status !== $uptodate) {
                    $data->status = $uptodate;
                    $data->save();
                    $this->sendNotification($data,  $uptodate, $today);
                }
            }

            foreach ($data->reminderDokumen as $reminder) {
                $daysArray = is_array($reminder->reminder_hari)
                    ? $reminder->reminder_hari
                    : (array) explode(',', $reminder->reminder_hari);

                foreach ($daysArray as $days) {
                    if (!is_numeric($days)) continue;
                    $reminderDate = $expiredDate->copy()->subDays((int) $days);

                    if (!empty($reminder->reminder_jam)) {
                        [$hour, $minute] = array_pad(explode(':', $reminder->reminder_jam), 2, 0);
                        $reminderDate->setTime((int) $hour, (int) $minute);
                    } else {
                        $reminderDate->startOfDay();
                    }

                    if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i')) {
                        $this->sendNotification($data, null, $today);
                        break 2;
                    }
                }
            }

            if ($today->greaterThanOrEqualTo($expiredDate) && $data->status !== $expired) {
                $data->status = $expired;
                $data->save();
                $this->sendNotification($data, $expired, $today);
            }
        } catch (\Throwable $th) {
            // Log::info($th);
        }
    }

    private function sendNotification($data, $status, $today)
    {
        $uptodate = 'uptodate';
        $nearExpiry = 'near expiry';
        $expired = 'expired';

        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_dokumen', 'manager_dokumen', 'operation_dokumen', 'super_admin', 'admin']);
        })->get();

        $title = 'Informasi Dokumen Pelayaran';
        $pesan = null;
        $subj = "Reminder Dokumen {$data->kapal->nama_kapal} - {$data->jenisDokumen->nama_jenis}";

        $expireds = Carbon::parse($data->historyDokumen->first()->tanggal_expired)
            ->format('d-M-Y');

        switch ($status) {
            case $uptodate:
                $pesan = "status dokumen {$data->jenisDokumen->nama_jenis} nomor {$data->historyDokumen->first()->nomor_dokumen}, kapal {$data->kapal->nama_kapal} telah diperbarui dengan status sekarang {$status}";
                break;
            case $nearExpiry:
                $pesan = "status dokumen {$data->jenisDokumen->nama_jenis} nomor {$data->historyDokumen->first()->nomor_dokumen}, kapal {$data->kapal->nama_kapal} akan segera berakhir pada {$expireds}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case $expired:
                $pesan = "status dokumen {$data->jenisDokumen->nama_jenis} nomor {$data->historyDokumen->first()->nomor_dokumen}, kapal {$data->kapal->nama_kapal} telah kadaluarsa pada {$expireds}. Segera lakukan tindakan untuk memperbarui dokumen.";
                break;
            default:
                $pesan = "status dokumen {$data->jenisDokumen->nama_jenis} nomor {$data->historyDokumen->first()->nomor_dokumen}, kapal {$data->kapal->nama_kapal} saat ini sudah hampir berakhir 30 hari sebelum expired. Segera lakukan pengecekan dan permbaruan jika diperlukan";
                break;
        }

        Notification::make()
            ->title($title)
            ->body($pesan)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url(url("/document/dokumens/$data->id")),
            ])
            ->sendToDatabase($recipient);


        $recipient->chunk(100)->each(function ($usersChunk) use ($pesan, $status, $title, $data) {
            foreach ($usersChunk as $user) {
                if (!$user->fcm_token) continue;
                SendFcmNotificationJob::dispatch(
                    $user->fcm_token,
                    $title,
                    $pesan,
                    [
                        'route' => '/doc/dashboard/dokumen/detail',
                        'id'    => (string) $data->id
                    ]
                );
            }
        });

        ToReminderDokumen::where('id_dokumen', $data->id)->chunk(100, function ($reminderChungs) use ($pesan, $status, $title, $today, $data, $subj, $expireds) {
            foreach ($reminderChungs as $reminders) {
                Log::info('send');
                if ($reminders->type == 'email') {
                    Mail::to($reminders->send_to)
                        ->queue(new MailServices(
                            nama: $reminders->nama,
                            url: url("/document/dokumens/$data->id"),
                            ceks: $pesan,
                            status: $status,
                            subj: $subj,
                            datetime: $today->format('d M Y'),
                        ));
                } else {
                    //  $variables = [
                    //      ['key' => '1', 'value' => 'nama', 'value_text' => $reminders->nama],
                    //      ['key' => '2', 'value' => 'no_doc', 'value_text' => $data->historyDokumen->first()->nomor_dokumen],
                    //      ['key' => '3', 'value' => 'tgl_exp', 'value_text' => $expireds],
                    //      ['key' => '4', 'value' => 'url', 'value_text' => url("/document/dokumens/$data->id")],
                    //  ];
                    //  try {
                    //      SendWhatsAppNotificationJob::dispatchSync(
                    //          $reminders->send_to,
                    //          $reminders->nama,
                    //          $variables
                    //      );
                    //
                    //      Log::info('Selesai dispatchSync');
                    //  } catch (\Throwable $th) {
                    //      Log::error('Gagal Dispatch: ' . $th->getMessage());
                    //  }
                }
            }
        });
    }
}
