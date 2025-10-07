<?php

namespace App\Services;

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

class CrewCertificatesReminderService
{
    public function updateAll(): void
    {
        $today = Carbon::now(config('app.timezone'));

        try {
            CrewCertificates::whereHas('applicant', function ($q) {
                $q->where('status_proses', 'Active');
            })
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
                return;
            }

            $expiredDate = Carbon::parse($doc->tanggal_expired, config('app.timezone'))->endOfDay();

            // 1. Cek Expired
            if ($today->format('Y-m-d H:i') === $expiredDate->format('Y-m-d H:i')) {
                $this->sendNotification($doc, 'telah berakhir');
            }
 

            // 2. Cek Reminder
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
                    }
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
                $q->where('name', 'staff_crew');
            })->get();

            Notification::make()
                ->title('Informasi Sertifikat Crew')
                ->body("Sertifikat {$doc->nama_sertifikat} dari {$doc->applicant->nama_crew} {$message}")
                ->success()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(url("/staff_crew/crew-overviews/{$doc->applicant->id}")),
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
