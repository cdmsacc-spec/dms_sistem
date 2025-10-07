<?php

namespace App\Services;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Filament\StaffDocument\Resources\NotificationResource;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentStatusChanged;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class DocumentStatusService
{
    public function updateAll(): void
    {
        $today = Carbon::today();

        Document::with(['expirations' => fn($q) => $q->latest(), 'reminders'])
            ->chunk(100, function ($documents) use ($today) {
                foreach ($documents as $document) {
                    $this->updateStatus($document, $today);
                }
            });
    }

    public function updateStatus(Document $document, Carbon $today = null): void
    {
        $today = Carbon::now(config('app.timezone'));
        $expiration = $document->expirations->first();

        if (!$expiration || !$expiration->tanggal_expired) {
            return;
        }

        $expiredDate = Carbon::parse($expiration->tanggal_expired, config('app.timezone'))
            ->endOfDay();

        $status = 'UpToDate';

        foreach ($document->reminders as $reminder) {
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



               if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i') &&
                    $document->status !== 'Near Expiry'
                ) {
                    $status = 'Near Expiry';
                    $this->sendNotification($document, $status);
                    break 2; // keluar dari semua loop reminder
                
                }
            }
        }

        if ($today->greaterThanOrEqualTo($expiredDate) && $document->status !== 'Expired') {
            $status = 'Expired';
            $this->sendNotification($document, $status);
        }

        if ($document->status !== $status) {
            $document->status = $status;
            $document->save();
        }
    }

    private function sendNotification($document, $status)
    {
        $recipient = User::whereHas('roles', function ($q) {
            $q->where('name', 'staff_document');
        })->get();

        Notification::make()
            ->title('Pembaruan Status Document')
            ->body('Status dari document ' . $document->nomor_dokumen . ' telah berubah menjadi ' . $status)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/staff_document/documents/'.$document->id")),
            ])
            ->sendToDatabase($recipient);
    }
}
