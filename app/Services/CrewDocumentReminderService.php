<?php

namespace App\Services;

use App\Enums\StatusDocumentFile;
use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Filament\StaffDocument\Resources\NotificationResource;
use App\Mail\ReminderMail;
use App\Models\CrewDocuments;
use App\Models\Document;
use App\Models\EmailReminderPkl;
use App\Models\PklReminder;
use App\Models\User;
use App\Notifications\DocumentStatusChanged;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class CrewDocumentReminderService
{
    public function updateAll(): void
    {
        $today = Carbon::now(config('app.timezone'));

        // CrewDocuments::whereHas('applicant', function ($q) {
        //     $q->where('status_proses', 'Active');
        // })
        CrewDocuments::whereHas('applicant')
            ->chunk(100, function ($documents) use ($today) {
                foreach ($documents as $doc) {
                    $this->updateStatus($doc, $today);
                }
            });
    }

    public function updateStatus(CrewDocuments $doc, Carbon $today): void
    {
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
        }
        if ($doc->status !== StatusDocumentFile::UpToDate->value) {
            $doc->update(['status' => StatusDocumentFile::UpToDate->value]);
            $this->sendNotification($doc, StatusDocumentFile::UpToDate->value,  $today);
        }
    }

    private function sendNotification(CrewDocuments $doc, string $status,  $today): void
    {
        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_crew', 'super_admin', 'manager_crew']);
        })->get();

        $title = 'Informasi Dokumen Crew';
        $pesan = null;

        switch ($status) {
            case StatusDocumentFile::UpToDate->value:
                $pesan = "status dokumen crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_document} telah diperbarui";
                break;
            case StatusDocumentFile::NearExpiry->value:
                $pesan = "status dokumen crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_document} akan segera berakhir pada {$doc->tanggal_expired}. Mohon diperiksa dan diperbarui jika diperlukan.";
                break;
            case StatusDocumentFile::Expired->value:
                $pesan = "status dokumen crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_document} telah kadaluarsa pada {$doc->tanggal_expired}. Segera lakukan tindakan untuk memperbarui dokumen.";
                break;

            default:
                $pesan = "status dokumen crew {$doc->applicant->nama_crew} dengan nomor {$doc->nomor_document} saat ini sudah hampir berakhir. Segera lakukan pengecekan dan permbaruan jika diperlukan";
                break;
        }

        Notification::make()
            ->title($title)
            ->body($pesan)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/crew/crew-all/{$doc->applicant->id}")),
            ])
            ->sendToDatabase($recipient);

        EmailReminderPkl::chunk(100, function ($emailsChunk) use ($today, $doc, $pesan, $status) {
            foreach ($emailsChunk as $mails) {
                Mail::to($mails->email)
                    ->queue(new ReminderMail(
                        nama: $mails->nama,
                        url: url("/crew/crew-all/{$doc->applicant->id}"),
                        ceks: $pesan,
                        status: $status,
                        datetime: $today->format('d M Y'),
                    ));
            }
        });
    }
}
