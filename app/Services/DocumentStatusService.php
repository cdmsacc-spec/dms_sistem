<?php

namespace App\Services;

use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Filament\StaffDocument\Resources\NotificationResource;
use App\Models\Document;
use App\Models\Lookup;
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
        $dateExpiry = Lookup::where('kategori', 'Document')
            ->where('code', 'Document Near Expiry')
            ->value('value');

        if (!$expiration || !$expiration->tanggal_expired) {
            return;
        }

        $expiredDate = Carbon::parse($expiration->tanggal_expired, config('app.timezone'))
            ->endOfDay();

        $status = 'UpToDate';

        if ($dateExpiry) {
            $nearExpiryDate = $expiredDate->copy()->subDays((int) $dateExpiry);
            if ($today->greaterThanOrEqualTo($nearExpiryDate) && $today->lessThan($expiredDate)) {
                if ($document->status !== 'Near Expiry') {
                    $document->status = 'Near Expiry';
                    $document->save();
                    $this->sendNotification($document, 'Near Expiry');
                }
            } elseif ($today->lessThan($nearExpiryDate)) {
                // Masih jauh dari Near Expiry → UpToDate
                if ($document->status !== 'UpToDate') {
                    $document->status = 'UpToDate';
                    $document->save();
                    $this->sendNotification($document, 'UpToDate');
                    // Opsional: bisa kirim notifikasi atau tidak
                }
            }
        }

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

                // Near Expiry
                if ($today->format('Y-m-d H:i') === $reminderDate->format('Y-m-d H:i')) {
                    $this->sendNotification($document, null);
                    break 2; // keluar dari semua loop reminders

                }
            }
        }

        if ($today->greaterThanOrEqualTo($expiredDate) && $document->status !== 'Expired') {
            $status = 'Expired';
            $this->sendNotification($document, $status);
            $document->status = $status;
            $document->save();
        }
    }

    private function sendNotification($document, $status)
    {
        $recipient = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff_document', 'super_admin', 'manager_document', 'operation']);
        })->get();

        $title = 'Pembaruan Status Document';
        $body = $status != null ? "Status document dengan nomor $document->nomor_dokumen telah berubah menjadi $status" : "Status document dengan nomor $document->nomor_dokumen hampir berakhir";
        Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(url("/document/documents/$document->id")),
            ])
            ->sendToDatabase($recipient);
    }
}
