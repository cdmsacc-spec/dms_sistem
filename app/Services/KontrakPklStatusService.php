<?php

namespace App\Services;

use App\Models\CrewPkl;
use App\Models\PklReminder;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

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
                if ($crewPkl->status_kontrak !== 'Expired') {
                    $crewPkl->update(['status_kontrak' => 'Expired']);
                    $this->sendNotification($crewPkl, 'telah berakhir');
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
                        $this->sendNotification($crewPkl, 'hampir berakhir');
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error('Error updateStatus PKL', [
                'crew_id' => $crewPkl->id,
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }
    }

    private function sendNotification(CrewPkl $crewPkl, string $message): void
    {
        try {
            $recipient = User::whereHas('roles', function ($q) {
                $q->where('name', 'staff_crew');
            })->get();

            Notification::make()
                ->title('Informasi Status Kontrak PKL')
                ->body("Kontrak PKL dengan nomor dokumen {$crewPkl->nomor_document} {$message}")
                ->success()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(url("/staff_crew/crew-pkls/{$crewPkl->id}/edit")),
                ])
                ->sendToDatabase($recipient);
        } catch (\Throwable $th) {
            \Log::error('Error updateStatus PKL', [
                'crew_id' => $crewPkl->id,
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }
    }
}
