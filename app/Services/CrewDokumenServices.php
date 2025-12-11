<?php

namespace App\Services;

use App\Jobs\SendFcmNotificationJob;
use App\Mail\MailServices;
use App\Models\CrewDokumen;
use App\Models\ReminderCrew;
use App\Models\ToReminderCrew;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class CrewDokumenServices
{
    public function updateAll()
    {
        CrewDokumen::with('crew')
            ->chunk(100, function ($dokumen) {
                foreach ($dokumen as $data) {
                    $this->updateStatus($data);
                }
            });
    }

    public function updateStatus(CrewDokumen $data)
    {
        try {
            $today = Carbon::now(config('app.timezone'));

            $uptodate = 'uptodate';
            $nearExpiry = 'near expiry';
            $expired = 'expired';

            $rectangleDate = 30;

            if (!$data->tanggal_expired) {
                if ($data->status !== 'uptodate') {
                    $data->update(['status' => $uptodate]);
                    $this->sendNotification($data, $uptodate, $today);
                }
                return;
            }

            $expiredDate = Carbon::parse($data->tanggal_expired, config('app.timezone'))->endOfDay();
            $nearExpiryDate = $expiredDate->copy()->subDays((int) $rectangleDate);

            if ($today->greaterThanOrEqualTo($expiredDate)) {
                if ($data->status !== $expired) {
                    $data->update(['status' => $expired]);
                    $this->sendNotification($data, $expired,  $today);
                }
                return;
            }

            if ($today->greaterThanOrEqualTo($nearExpiryDate) && $today->lessThan($expiredDate)) {
                if ($data->status !== $nearExpiry) {
                    $data->update(['status' => $nearExpiry]);
                    $this->sendNotification($data, $nearExpiry,  $today);
                }
                return;
            }

            $reminders = ReminderCrew::all();
            foreach ($reminders as $reminder) {
                $daysArray = explode(',', $reminder->reminder_hari);

                foreach ($daysArray as $days) {
                    if (!is_numeric($days)) continue;

                    $reminderDate = $expiredDate->copy()->subDays((int)$days);

                    if ($reminder->reminder_jam) {
                        [$hour, $minute] = explode(':', $reminder->reminder_jam);
                        $reminderDate->setTime((int)$hour, (int)$minute);
                    } else {
                        $reminderDate->startOfDay();
                    }

                    if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i')) {
                        $this->sendNotification($data, null,  $today);
                        return;
                    }
                }
            }

            if ($data->status !== $uptodate) {
                $data->update(['status' => $uptodate]);
                $this->sendNotification($data, $uptodate,  $today);
                return;
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
            $q->whereIn('name', ['staff_crew', 'manager_crew', 'super_admin', 'admin']);
        })->get();

        $title = 'Informasi Dokumen Crew';
        $pesan = null;

        switch ($status) {
            case $uptodate:
                $pesan = "status data dokumen crew {$data->crew->nama_crew}, dengan nomor dokumen {$data->nomor_dokumen}, jenis dokumen {$data->jenis_dokumen} telah diperbarui";
                break;
            case $nearExpiry:
                $pesan = "status data dokumen crew {$data->crew->nama_crew}, dengan nomor dokumen {$data->nomor_dokumen}, jenis dokumen {$data->jenis_dokumen} akan segera berakhir pada {$data->tanggal_expired}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case $expired:
                $pesan = "status data dokumen crew {$data->crew->nama_crew}, dengan nomor dokumen {$data->nomor_dokumen}, jenis dokumen {$data->jenis_dokumen} telah kadaluarsa pada {$data->tanggal_expired}. Segera lakukan tindakan untuk memperbarui dokumen.";
                break;
            default:
                $pesan = "status data dokumen crew {$data->crew->nama_crew}, dengan nomor dokumen {$data->nomor_dokumen}, jenis dokumen {$data->jenis_dokumen} saat ini sudah hampir berakhir 30 hari sebelum expired. Segera lakukan pengecekan dan permbaruan jika diperlukan";
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
                    ->url(url("/crew/all-crews/{$data->crew->id}")),
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
                        'route' => '/crew/dashboard/crews/detail',
                        'id'    => (string)$data->crew->id
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
                            url: url("/crew/all-crews/{$data->crew->id}"),
                            ceks: $pesan,
                            status: $status,
                            datetime: $today->format('d M Y'),
                        ));
                }
            }
        });
    }
}
