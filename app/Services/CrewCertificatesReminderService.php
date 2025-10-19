<?php

namespace App\Services;

use App\Enums\StatusDocumentFile;
use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Filament\StaffDocument\Resources\NotificationResource;
use App\Models\CrewCertificates;
use App\Models\CrewDocuments;
use App\Models\Document;
use App\Models\PklReminder;
use App\Models\User;
use App\Notifications\DocumentStatusChanged;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
                    $this->sendNotification($doc,  StatusDocumentFile::UpToDate->value);
                }
                return;
            }

            $expiredDate = Carbon::parse($doc->tanggal_expired, config('app.timezone'))->endOfDay();
            // --- 1. Cek jika sudah EXPPIRED ---
            if ($today->greaterThanOrEqualTo($expiredDate)) {
                if ($doc->status !== StatusDocumentFile::Expired->value) {
                    $doc->update(['status' => StatusDocumentFile::Expired->value]);
                    $this->sendNotification($doc, 'telah berakhir');
                }
                return;
            }

            // --- 2. Cek apakah sudah masuk 30 hari sebelum expired ---
            $thirtyDaysBefore = $expiredDate->copy()->subDays(30);

            if ($today->greaterThanOrEqualTo($thirtyDaysBefore) && $today->lessThan($expiredDate)) {
                if ($doc->status !== StatusDocumentFile::NearExpiry->value) {
                    $doc->update(['status' => StatusDocumentFile::NearExpiry->value]);
                    $this->sendNotification($doc, 'akan segera berakhir (kurang dari 30 hari status near expiry)');
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
                        $this->sendNotification($doc, 'hampir berakhir');
                        return;
                    }
                }
                if ($doc->status !== StatusDocumentFile::UpToDate->value) {
                    $doc->update(['status' => StatusDocumentFile::UpToDate->value]);
                    $this->sendNotification($doc, 'UpToDate');
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

    private function sendNotification(CrewCertificates $doc, string $message): void
    {
        try {

            $recipient = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['staff_crew', 'super_admin', 'manager_crew']);
            })->get();

            Notification::make()
                ->title('Informasi Sertifikat Crew')
                ->body("Sertifikat {$doc->nama_sertifikat} dari {$doc->applicant->nama_crew} {$message}")
                ->success()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(url("/crew/crew-all/{$doc->applicant->id}?activeRelationManager=1")),
                ])
                ->sendToDatabase($recipient);
        } catch (\Throwable $th) {
            \Log::error('ERROR REMINDER', [
                "message" => $th->getMessage(),
                "line"    => $th->getLine(),
                "file"    => $th->getFile(),
            ]);
        }
    }
}
