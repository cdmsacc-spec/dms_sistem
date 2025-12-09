<?php

namespace App\Services;

use App\Jobs\SendFcmNotificationJob;
use App\Mail\MailServices;
use App\Models\CrewKontrak;
use App\Models\ReminderCrew;
use App\Models\ToReminderCrew;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CrewKontrakServices
{
    public function updateAll(): void
    {
        CrewKontrak::where('status_kontrak', 'active')->with(['crew', 'perusahaan', 'kapal'])
            ->chunk(100, function ($kontrak) {
                foreach ($kontrak as $data) {
                    $this->updateStatus($data);
                }
            });
    }

    public function updateStatus(CrewKontrak $data): void
    {
        $today = Carbon::now(config('app.timezone'));

        $uptodate = 'uptodate';
        $nearExpiry = 'near expiry';
        $expired = 'expired';

        $rectangleDate = 30;

        $endDate = $data->end_date
            ? Carbon::parse($data->end_date, config('app.timezone'))->endOfDay()
            : null;

        if (!$endDate) {
            return;
        }

        if ($today->greaterThanOrEqualTo($endDate)) {
            if ($data->status_kontrak !== $expired) {
                $data->update(['status_kontrak' => $expired]);
                $this->sendNotification($data, $expired,  $today);
            }
            return;
        }

        $nearExpiryDate = $endDate->copy()->subDays((int) $rectangleDate);

        if ($today->greaterThanOrEqualTo($nearExpiryDate) && $today->lessThan($endDate)) {
            if ($data->near_expiry !== true) {
                $data->update(['near_expiry' => true]);
                $this->sendNotification($data, $nearExpiry,  $today);
            }
            return;
        }

        $reminders = ReminderCrew::all();

        foreach ($reminders as $reminder) {
            $daysArray = is_array($reminder->reminder_hari)
                ? $reminder->reminder_hari
                : explode(',', $reminder->reminder_hari);

            foreach ($daysArray as $days) {
                if (!is_numeric($days)) continue;

                $reminderDate = $endDate->copy()->subDays((int) $days);

                if (!empty($reminder->reminder_jam)) {
                    [$hour, $minute] = array_pad(explode(':', $reminder->reminder_jam), 2, 0);
                    $reminderDate->setTime((int) $hour, (int) $minute);
                } else {
                    $reminderDate->startOfDay();
                }
                // Log::info('reminder:');
                // Log::info($reminderDate->format('Y-m-d H:i'));
                // Log::info('today:');
                // Log::info($today->format('Y-m-d H:i'));

                if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i')) {
                    $this->sendNotification($data, null,  $today);
                }
            }
        }

        if ($data->near_expiry !== false) {
            $data->update(['near_expiry' => false]);
            $this->sendNotification($data, $uptodate,  $today);
        }
    }

    private function sendNotification($data, $status, $today)
    {
        $uptodate = 'uptodate';
        $nearExpiry = 'near expiry';
        $expired = 'expired';

        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_crew', 'manager_crew', 'super_admin', 'admin']);
        })->get();

        $title = 'Informasi Kontrak Crew';
        $pesan = null;

        switch ($status) {
            case $uptodate:
                $pesan = "status kontrak crew {$data->crew->nama_crew}, dengan nomor kontrak {$data->nomor_dokumen}, perusahaan {$data->perusahaan->nama_perusahaan}, kapal {$data->kapal->nama_kapal} telah diperbarui";
                break;
            case $nearExpiry:
                $pesan = "status kontrak crew {$data->crew->nama_crew}, dengan nomor kontrak {$data->nomor_dokumen}, perusahaan {$data->perusahaan->nama_perusahaan}, kapal {$data->kapal->nama_kapal} akan segera berakhir pada {$data->end_date}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case $expired:
                $pesan = "status kontrak crew {$data->crew->nama_crew}, dengan nomor kontrak {$data->nomor_dokumen}, perusahaan {$data->perusahaan->nama_perusahaan}, kapal {$data->kapal->nama_kapal} telah kadaluarsa pada {$data->end_date}. Segera lakukan tindakan untuk memperbarui kontrak dokumen.";
                break;
            default:
                $pesan = "status kontrak crew {$data->crew->nama_crew}, dengan nomor kontrak {$data->nomor_dokumen}, perusahaan {$data->perusahaan->nama_perusahaan}, kapal {$data->kapal->nama_kapal} saat ini sudah hampir berakhir 30 hari sebelum expired. Segera lakukan pengecekan dan permbaruan jika diperlukan";
                break;
        }

        Notification::make()
            ->title($title)
            ->body($pesan)
            ->success()

            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/crew/all-crews/{$data->id}/detail_kontak")),
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
                        'route' => '/crew/dashboard/kontrak/detail',
                        'id'    => (string)$data->id
                    ]
                );
            }
        });

        ToReminderCrew::chunk(100, function ($reminderChungs) use ($pesan, $status, $title, $today, $data) {
            foreach ($reminderChungs as $reminders) {
                if ($reminders->type == 'email') {
                    Mail::to($reminders->send_to)
                        ->queue(new MailServices(
                            nama: $reminders->nama,
                            url: url("/crew/all-crews/{$data->id}/detail_kontak"),
                            ceks: $pesan,
                            status: $status,
                            datetime: $today->format('d M Y'),
                        ));
                }
            }
        });
    }
}
