<?php

namespace App\Filament\Document\Resources\Dokumens\Pages;

use App\Filament\Document\Resources\Dokumens\DokumenResource;
use App\Filament\Document\Resources\Dokumens\Schemas\DokumenByKapalForm;
use App\Models\Dokumen;
use App\Models\HistoryDokumen;
use App\Models\Kapal;
use App\Models\ReminderDokumen;
use App\Models\ReminderTemplate;
use App\Models\ToReminderDokumen;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateDokumenByKapal extends CreateRecord
{
    protected static string $resource = DokumenResource::class;

    public function getTitle(): string
    {
        return 'Create Dokumen by Kapal';
    }

    public function form(Schema $schema): Schema
    {
        return DokumenByKapalForm::configure($schema);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $kapalId    = $data['id_kapal'] ?? null;
        $items      = $data['dokumen_items'] ?? [];
        $reminder_dokumens = $data['reminder_dokumen'] ?? [];
        $to_reminder_dokumens = $data['to_reminder_dokumen'] ?? [];
        $template = 
            [
                "save_as_template" => $data['save_as_template'] ?? false, 
                "template_name" => $data['template_name'] ?? null
            ];

        $lastDokumen = null;

        try {
            DB::transaction(function () use ($kapalId, $items, &$lastDokumen, $reminder_dokumens, $to_reminder_dokumens, $template) {
                foreach ($items as $item) {

                    // ── 1. Buat Dokumen ────────────────────────────────────────────
                    $dokumen = Dokumen::create([
                        'id_kapal'          => $kapalId,
                        'id_jenis_dokumen'  => $item['id_jenis_dokumen'],
                        'penerbit'          => $item['penerbit'] ?? null,
                        'tempat_penerbitan' => $item['tempat_penerbitan'] ?? null,
                        'keterangan'        => $item['keterangan'] ?? null,
                        'id_author'         => auth()->id(),
                        'status'            => 'uptodate',
                    ]);

                    // ── 2. Buat HistoryDokumen ─────────────────────────────────────
                    HistoryDokumen::create([
                        'id_dokumen'      => $dokumen->id,
                        'tanggal_terbit'  => $item['tanggal_terbit'],
                        'tanggal_expired' => $item['tanggal_expired'] ?? null,
                        'nomor_dokumen'   => $item['nomor_dokumen'],
                        'file'            => $item['file'],
                    ]);

                    // ── 3. Simpan Reminder (hanya jika ada tanggal_expired) ────────
                    if (!empty($item['tanggal_expired'])) {
                        // Jadwal reminder 
                        foreach ($reminder_dokumens ?? [] as $reminder) {
                            ReminderDokumen::create([
                                'id_dokumen'   => $dokumen->id,
                                'reminder_hari' => $reminder['reminder_hari'],
                                'reminder_jam'  => $reminder['reminder_jam'],
                            ]);
                        }

                        // Penerima reminder
                        foreach ($to_reminder_dokumens ?? [] as $to) {
                            $reminder = ToReminderDokumen::create([
                                'id_dokumen' => $dokumen->id,
                                'nama'       => $to['nama'],
                                'send_to'    => $to['send_to'],
                                'type'       => $to['type'],
                            ]);
                        }
                        
                    }
                    $lastDokumen = $dokumen;
                }

                // ── 4. Simpan sebagai Template (opsional) ──────────────────
                if ($template['save_as_template'] && !empty($template['template_name'])) {
                    $template = ReminderTemplate::create([
                        'nama_template' => $template['template_name'],
                        'id_author'     => auth()->id(),
                    ]);

                    foreach ($reminder_dokumens ?? [] as $reminder) {
                        $template->reminderItems()->create([
                            'reminder_hari' => $reminder['reminder_hari'],
                            'reminder_jam'  => $reminder['reminder_jam'],
                        ]);
                    }

                    foreach ($to_reminder_dokumens ?? [] as $to) {
                        $template->toReminderItems()->create([
                            'nama'    => $to['nama'],
                            'send_to' => $to['send_to'],
                            'type'    => $to['type'],
                        ]);
                    }
                }
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'dokumen_items' => 'Salah satu nomor dokumen sudah digunakan. Periksa kembali isian nomor dokumen.',
                ]);
            }

            throw $e;
        }

        return $lastDokumen ?? Dokumen::create([
            'id_kapal'  => $kapalId,
            'id_author' => auth()->id(),
            'status'    => 'uptodate',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}