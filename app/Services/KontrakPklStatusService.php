<?php

namespace App\Services;

use App\Enums\StatusDocumentFile;
use App\Enums\StatusKontrakCrew;
use App\Mail\ReminderMail;
use App\Models\CrewPkl;
use App\Models\EmailReminderPkl;
use App\Models\PklReminder;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class KontrakPklStatusService
{
    public function updateAll(): void
    {
        // Ambil waktu sekarang sesuai timezone app
        $today = Carbon::now(config('app.timezone'));

        CrewPkl::where('status_kontrak', 'Active')
            ->chunk(100, function ($crewPkls) use ($today) {
                foreach ($crewPkls as $crewPkl) {
                    $this->updateStatus($crewPkl, $today);
                }
            });
    }

    public function updateStatus(CrewPkl $crewPkl, Carbon $today = null): void
    {
        try {
            $today = $today ?? Carbon::now(config('app.timezone'));
            $endDate = $crewPkl->end_date
                ? Carbon::parse($crewPkl->end_date, config('app.timezone'))->endOfDay()
                : null;

            if (!$endDate) {
                return; // skip kalau tidak ada end_date
            }

            // === 1. CEK STATUS EXPIRED ===
            if ($today->greaterThanOrEqualTo($endDate)) {
                if ($crewPkl->status_kontrak !== StatusKontrakCrew::Expired->value) {
                    $crewPkl->update(['status_kontrak' => 'Expired']);
                    $this->sendNotification($crewPkl, StatusDocumentFile::Expired->value,  $today);
                }
                return;
            }

            // --- 2. Cek apakah sudah masuk 30 hari sebelum expired ---
            $thirtyDaysBefore = $endDate->copy()->subDays(30);

            if ($today->greaterThanOrEqualTo($thirtyDaysBefore) && $today->lessThan($endDate)) {
                if ($crewPkl->isNearExpiry !== true) {
                    $crewPkl->update(['isNearExpiry' => true]);
                    $this->sendNotification($crewPkl, StatusDocumentFile::NearExpiry->value,  $today);
                }
                return;
            }

            // === 2. CEK REMINDER (H-30, H-7, H-1 dst) ===
            $reminders = PklReminder::all();
            foreach ($reminders as $reminder) {
                $daysArray = is_array($reminder->reminder_hari)
                    ? $reminder->reminder_hari
                    : explode(',', $reminder->reminder_hari);

                foreach ($daysArray as $days) {
                    if (!is_numeric($days)) continue;

                    // Hitung tanggal reminder
                    $reminderDate = $endDate->copy()->subDays((int) $days);

                    if (!empty($reminder->reminder_jam)) {
                        [$hour, $minute] = array_pad(explode(':', $reminder->reminder_jam), 2, 0);
                        $reminderDate->setTime((int) $hour, (int) $minute);
                    } else {
                        $reminderDate->startOfDay();
                    }

                    if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i')) {
                        $this->sendNotification($crewPkl, 'reminder',  $today);
                    }
                }
            }

            if ($crewPkl->isNearExpiry !== false) {
                $crewPkl->update(['isNearExpiry' => false]);
                $this->sendNotification($crewPkl, StatusDocumentFile::UpToDate->value,  $today);
            }
        } catch (\Throwable $th) {
            \Log::error('Error updateStatus PKL', [
                'crew_id' => $crewPkl->id,
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }
    }

    private function sendNotification(CrewPkl $crewPkl, $status, $today): void
    {

        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_crew', 'super_admin', 'manager_crew']);
        })->get();

        $title = 'Informasi Status Kontrak PKL';
        $pesan = null;

        switch ($status) {
            case StatusDocumentFile::UpToDate->value:
                $pesan = "status kontrak crew {$crewPkl->crew->nama_crew} dengan nomor {$crewPkl->nomor_document}, perusahaan {$crewPkl->perusahaan->nama_perusahaan}, kapal {$crewPkl->kapal->nama_kapal} telah diperbarui";
                break;
            case StatusDocumentFile::NearExpiry->value:
                $pesan = "status kontrak crew {$crewPkl->crew->nama_crew} dengan nomor {$crewPkl->nomor_document}, perusahaan {$crewPkl->perusahaan->nama_perusahaan}, kapal {$crewPkl->kapal->nama_kapal} akan segera berakhir pada {$crewPkl->tanggal_expired}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case StatusDocumentFile::Expired->value:
                $pesan = "status kontrak crew {$crewPkl->crew->nama_crew} dengan nomor {$crewPkl->nomor_document}, perusahaan {$crewPkl->perusahaan->nama_perusahaan}, kapal {$crewPkl->kapal->nama_kapal} telah kadaluarsa pada {$crewPkl->tanggal_expired}. Segera lakukan tindakan untuk memperbarui kontrak dokumen.";
                break;

            default:
                $pesan = "status kontrak crew {$crewPkl->crew->nama_crew} dengan nomor {$crewPkl->nomor_document}, perusahaan {$crewPkl->perusahaan->nama_perusahaan}, kapal {$crewPkl->kapal->nama_kapal} saat ini sudah hampir berakhir. Segera lakukan pengecekan dan permbaruan jika diperlukan";
                break;
        }

        Notification::make()
            ->title($title)
            ->body($pesan)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/crew/crew-all/{$crewPkl->id}/detail_kontak_pkl")),
            ])
            ->sendToDatabase($recipient);

        EmailReminderPkl::chunk(100, function ($emailsChunk) use ($today, $crewPkl, $pesan, $status) {
            foreach ($emailsChunk as $mails) {
                Mail::to($mails->email)
                    ->queue(new ReminderMail(
                        nama: $mails->nama,
                        url: url("/crew/crew-all/{$crewPkl->id}/detail_kontak_pkl"),
                        ceks: $pesan,
                        status: $status,
                        datetime: $today->format('d M Y'),
                    ));
            }
        });
    }
}
