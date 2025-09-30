<?php

namespace App\Filament\StaffDocument\Resources\DocumentResource\Pages;

use App\Filament\StaffDocument\Resources\DocumentExpirationResource;
use App\Filament\StaffDocument\Resources\DocumentResource;
use App\Models\DocumentExpiration;
use App\Models\DocumentReminder;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Carbon;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected static ?string $title = 'View Document';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('History Document')
                ->icon('heroicon-o-bars-3')
                ->color('warning')
                ->size(ActionSize::Small)
                ->url(fn($record) => ViewDocumentHistories::getUrl(['record' => $record])),
            Action::make('exparation')
                ->label('History Exparation')
                ->icon('heroicon-o-bars-3')
                ->color('info')
                ->size(ActionSize::Small)
                ->url(fn($record) => ViewDocumentExpiration::getUrl(['record' => $record])),
            Action::make('edit')
                ->label('Renew Document')
                ->color('success')
                ->icon('heroicon-o-pencil')
                ->size(ActionSize::Small)
                ->url(fn($record) => DocumentResource::getUrl('edit', ['record' => $record, 'renew' => true])),
        ];
    }
    
    /**
     * Konfigurasi tampilan detail (Infolist) untuk Document.
     *
     * Berisi beberapa tab dan section:
     * - Tab Perusahaan: Info perusahaan pemilik kapal.
     * - Tab Kapal: Info kapal terkait.
     * - Grid utama: Info dokumen (nomor, jenis, penerbit, tanggal).
     * - Section keterangan & reminder: Keterangan tambahan + reminder.
     * - Section status & file: Status dokumen, file terkait, dan pembuat.
     * - Section komentar: Komentar menggunakan plugin filament-comments.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // === Tabs Perusahaan & Kapal ===
            Tabs::make('Tabs')
                ->tabs([
                    // Tab Perusahaan
                    Tabs\Tab::make('Perusahaan')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            TextEntry::make('kapal.perusahaan.nama_perusahaan')
                                ->label('Perusahaan'),
                            TextEntry::make('kapal.perusahaan.email')
                                ->label('Email'),
                            TextEntry::make('kapal.perusahaan.telepon')
                                ->label('Telepon'),
                            TextEntry::make('kapal.perusahaan.npwp')
                                ->label('NPWP'),
                        ])
                        ->columns(2),

                    // Tab Kapal
                    Tabs\Tab::make('Kapal')
                        ->icon('heroicon-o-paper-airplane')
                        ->schema([
                            TextEntry::make('kapal.nama_kapal')
                                ->label('Kapal'),
                            TextEntry::make('kapal.jenisKapal.nama_jenis')
                                ->label('Jenis'),
                            TextEntry::make('kapal.tahun_kapal')
                                ->label('Tahun'),
                            TextEntry::make('kapal.status_certified')
                                ->label('Status Certified'),
                        ])
                        ->columns(2),
                ]),

            // === Info dasar dokumen ===
            Grid::make(2)
                ->schema([
                    TextEntry::make('nomor_dokumen'),
                    TextEntry::make('jenisDocument.nama_dokumen'),
                    TextEntry::make('penerbit'),
                    TextEntry::make('tempat_penerbitan'),

                    // Tanggal terbit -> ambil dari DocumentExpiration terakhir
                    TextEntry::make('tanggal_terbit')
                        ->getStateUsing(function ($record) {
                            if (!$record) {
                                return '-';
                            }
                            $data = DocumentExpiration::where('document_id', $record->id)
                                ->orderByDesc('id') // lebih aman daripada latest()
                                ->first();

                            return $data?->tanggal_terbit
                                ? Carbon::parse($data->tanggal_terbit)->format('d-m-Y')
                                : '-';
                        }),

                    // Tanggal expired -> ambil dari DocumentExpiration terakhir
                    TextEntry::make('tanggal_expired')
                        ->getStateUsing(function ($record) {
                            if (!$record) {
                                return '-';
                            }
                            $data = DocumentExpiration::where('document_id', $record->id)
                                ->orderByDesc('id')
                                ->first();

                            return $data?->tanggal_expired ?? 'Tidak ada expired';
                        }),
                ])
                ->columnSpan(1),

            // === Keterangan & Reminder ===
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('keterangan')
                                ->label('Keterangan'),

                            // Reminder jadwal notifikasi
                            TextEntry::make('reminders.id')
                                ->label('Reminder')
                                ->badge()
                                ->color('warning')
                                ->placeholder('Tanpa Reminder')
                                ->formatStateUsing(function ($state, $record) {
                                    if (!$state) {
                                        return null;
                                    }

                                    $expiration = DocumentExpiration::where('document_id', $record->id)
                                        ->latest()
                                        ->first();

                                    if (!$expiration || !$expiration->tanggal_expired) {
                                        return null;
                                    }

                                    $ids = is_array($state) ? $state : [$state];
                                    $reminders = DocumentReminder::whereIn('id', $ids)->get();

                                    $datesWithTime = $reminders->map(function ($reminder) use ($expiration) {
                                        $daysArray = is_array($reminder->reminder_hari)
                                            ? $reminder->reminder_hari
                                            : explode(',', $reminder->reminder_hari);

                                        return collect($daysArray)->map(function ($days) use ($expiration, $reminder) {
                                            $date = Carbon::parse($expiration->tanggal_expired)
                                                ->subDays((int) $days)
                                                ->format('d-m-Y');

                                            $time = $reminder->reminder_jam ?? '';

                                            // Hapus offset +07 dari jam jika ada
                                            return $date . '   ' . preg_replace('/\+\d+$/', '', $time);
                                        });
                                    })->flatten();

                                    return $datesWithTime->implode(', ');
                                }),
                        ]),
                ]),

            // === Status, File, & CreatedBy ===
            Section::make()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            // Status dokumen
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'UpToDate'    => 'success',
                                    'Near Expiry' => 'warning',
                                    default       => 'danger',
                                }),

                            // File dokumen (dari DocumentExpiration terbaru)
                            TextEntry::make('file_path')
                                ->label('File Dokumen')
                                ->icon('heroicon-o-document-text')
                                ->getStateUsing(
                                    fn($record) =>
                                    DocumentExpiration::where('document_id', $record->id)
                                        ->latest()
                                        ->first()?->file_path
                                )
                                ->formatStateUsing(
                                    fn($state) =>
                                    $state ? 'Document File' : 'Tidak ada file'
                                )
                                ->color(
                                    fn($state) =>
                                    $state ? 'info' : 'danger'
                                )
                                ->url(
                                    fn($state) =>
                                    asset('storage/' . $state),
                                    shouldOpenInNewTab: true
                                ),

                            // Dibuat oleh
                            TextEntry::make('createdBy.name')
                                ->label('Created By')
                                ->badge()
                                ->icon('heroicon-o-user')
                                ->color('success'),
                        ]),
                ]),

            // === Komentar Dokumen ===
            Section::make('Komentar')
                ->schema([
                    CommentsEntry::make('filament_comments')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
