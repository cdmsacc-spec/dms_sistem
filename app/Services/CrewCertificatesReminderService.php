<?php

namespace App\Services;

use App\Enums\StatusDocumentFile;
use App\Mail\ReminderMail;
use App\Models\CrewCertificates;
use App\Models\EmailReminderPkl;
use App\Models\PklReminder;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CrewCertificatesReminderService
{
    public function updateAll(): void
    {
        $today = Carbon::now(config('app.timezone'));
        try {
            CrewCertificates::whereHas('applicant')
                ->chunk(100, function ($documents) use ($today) {
                    foreach ($documents as $doc) {
                        $this->updateStatus($doc, $today);
                    }
                });
        } catch (\Throwable $th) {
            \Log::error('ERROR REMINDER', [
                "message" => $th->getMessage(),
                "line"    => $th->getLine(),
                "file"    => $th->getFile(),
            ]);
        }
    }

    public function updateStatus(CrewCertificates $doc, Carbon $today): void
    {
        try {
            if (!$doc->tanggal_expired) {
                if ($doc->status !== StatusDocumentFile::UpToDate->value) {
                    $doc->update(['status' => StatusDocumentFile::UpToDate->value]);
                    $this->sendNotification($doc,  StatusDocumentFile::UpToDate->value,  $today);
                }
                return;
            }

            $expiredDate = Carbon::parse($doc->tanggal_expired, config('app.timezone'))->endOfDay();
            // --- 1. Cek jika sudah EXPPIRED ---
            if ($today->greaterThanOrEqualTo($expiredDate)) {
                if ($doc->status !== StatusDocumentFile::Expired->value) {
                    $doc->update(['status' => StatusDocumentFile::Expired->value]);
                    $this->sendNotification($doc, StatusDocumentFile::Expired->value,  $today);
                }
                return;
            }

            // --- 2. Cek apakah sudah masuk 30 hari sebelum expired ---
            $thirtyDaysBefore = $expiredDate->copy()->subDays(30);

            if ($today->greaterThanOrEqualTo($thirtyDaysBefore) && $today->lessThan($expiredDate)) {
                if ($doc->status !== StatusDocumentFile::NearExpiry->value) {
                    $doc->update(['status' => StatusDocumentFile::NearExpiry->value]);
                    $this->sendNotification($doc, StatusDocumentFile::NearExpiry->value,  $today);
                }
                return;
            }


            // --- 3. Cek Reminder Manual (PklReminder) ---
            $reminders = PklReminder::all();
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
                        $this->sendNotification($doc, 'reminder',  $today);
                        return;
                    }
                }
                if ($doc->status !== StatusDocumentFile::UpToDate->value) {
                    $doc->update(['status' => StatusDocumentFile::UpToDate->value]);
                    $this->sendNotification($doc, StatusDocumentFile::UpToDate->value,  $today);
                }
            }
        } catch (\Throwable $th) {
            \Log::error('ERROR REMINDER', [
                "message" => $th->getMessage(),
                "line"    => $th->getLine(),
                "file"    => $th->getFile(),
            ]);
        }
    }

    private function sendNotification(CrewCertificates $doc, string $status,  $today): void
    {


        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_crew', 'super_admin', 'manager_crew']);
        })->get();

        $title = 'Informasi Sertifikat Crew';
        $pesan = null;

        switch ($status) {
            case StatusDocumentFile::UpToDate->value:
                $pesan = "status certificate crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_sertifikat} telah diperbarui";
                break;
            case StatusDocumentFile::NearExpiry->value:
                $pesan = "status certificate crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_sertifikat} akan segera berakhir pada {$doc->tanggal_expired}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case StatusDocumentFile::Expired->value:
                $pesan = "status certificate crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_sertifikat} telah kadaluarsa pada {$doc->tanggal_expired}. Segera lakukan tindakan untuk memperbarui sertificate.";
                break;

            default:
                $pesan = "status certificate crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_sertifikat} saat ini sudah hampir berakhir. Segera lakukan pengecekan dan permbaruan jika diperlukan";
                break;
        }

        Notification::make()
            ->title($title)
            ->body($pesan)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/crew/crew-all/{$doc->applicant->id}?activeRelationManager=1")),
            ])
            ->sendToDatabase($recipient);

        EmailReminderPkl::chunk(100, function ($emailsChunk) use ($today, $doc, $pesan, $status) {
            foreach ($emailsChunk as $mails) {
                Mail::to($mails->email)
                    ->queue(new ReminderMail(
                        nama: $mails->nama,
                        url: url("/crew/crew-all/{$doc->applicant->id}?activeRelationManager=1"),
                        ceks: $pesan,
                        status: $status,
                        datetime: $today->format('d M Y'),
                    ));
            }
        });
    }
}
