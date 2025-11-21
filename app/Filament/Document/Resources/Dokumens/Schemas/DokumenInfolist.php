<?php

namespace App\Filament\Document\Resources\Dokumens\Schemas;

use App\Models\HistoryDokumen;
use App\Models\ReminderDokumen;
use App\Models\ToReminderDokumen;
use App\Models\User;
use Carbon\Carbon;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class DokumenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Perusahaan')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextEntry::make('kapal.perusahaan.nama_perusahaan')
                                    ->label('Perusahaan'),
                                TextEntry::make('kapal.perusahaan.email')
                                    ->label('Email'),
                                TextEntry::make('kapal.perusahaan.telp')
                                    ->label('Telepon'),
                                TextEntry::make('kapal.perusahaan.npwp')
                                    ->label('NPWP'),
                            ])
                            ->columns(2),

                        Tab::make('Kapal')
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

                Grid::make(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('latestHistory.nomor_dokumen'),
                        TextEntry::make('jenisDokumen.nama_jenis'),
                        TextEntry::make('penerbit'),
                        TextEntry::make('tempat_penerbitan'),
                        TextEntry::make('tanggal_terbit')
                            ->getStateUsing(function ($record) {
                                if (!$record) {
                                    return '-';
                                }
                                $data = HistoryDokumen::where('id_dokumen', $record->id)
                                    ->orderByDesc('id') // lebih aman daripada latest()
                                    ->first();

                                return $data?->tanggal_terbit
                                    ? Carbon::parse($data->tanggal_terbit)->format('d-m-Y')
                                    : '-';
                            }),

                        TextEntry::make('tanggal_expired')
                            ->getStateUsing(function ($record) {
                                if (!$record) {
                                    return '-';
                                }
                                $data = HistoryDokumen::where('id_dokumen', $record->id)
                                    ->orderByDesc('id')
                                    ->first();

                                return $data?->tanggal_expired ?? 'Tidak ada expired';
                            }),
                    ]),

                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextEntry::make('reminderDokumen.id')
                                    ->label('Reminder Time')
                                    ->badge()
                                    ->color('warning')
                                    ->columnSpanFull()
                                    ->placeholder('Tanpa Reminder')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (!$state) {
                                            return null;
                                        }
                                        $expiration = HistoryDokumen::where('id_dokumen', $record->id)
                                            ->latest()
                                            ->first();

                                        if (!$expiration || !$expiration->tanggal_expired) {
                                            return null;
                                        }

                                        $ids = is_array($state) ? $state : [$state];
                                        $reminders = ReminderDokumen::whereIn('id', $ids)->get();
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

                                TextEntry::make('created_at')
                                    ->label('Reminder Email')
                                    ->badge()
                                    ->columnSpan(2)
                                    ->color('warning')
                                    ->placeholder('Tanpa Reminder')
                                    ->formatStateUsing(function ($state, $record) {
                                        $emails = $record->toReminderDokumen()
                                            ->where('type', 'email')
                                            ->pluck('send_to')
                                            ->toArray();
                                        return !empty($emails)
                                            ? implode(', ', $emails)
                                            : 'Tanpa Reminder';
                                    }),

                                TextEntry::make('updated_at')
                                    ->label('Reminder Whatsaap')
                                    ->badge()
                                    ->columnSpan(2)
                                    ->color('warning')
                                    ->placeholder('Tanpa Reminder')
                                    ->formatStateUsing(function ($state, $record) {
                                        $emails = $record->toReminderDokumen()
                                            ->where('type', 'wa')
                                            ->pluck('send_to')
                                            ->toArray();
                                        return !empty($emails)
                                            ? implode(', ', $emails)
                                            : 'Tanpa Reminder';
                                    })
                            ]),
                    ]),

                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'uptodate'    => 'success',
                                        'near expiry' => 'warning',
                                        default       => 'danger',
                                    }),

                                // File dokumen (dari DocumentExpiration terbaru)
                                TextEntry::make('file')
                                    ->label('File Dokumen')
                                    ->icon('heroicon-o-document-text')
                                    ->badge()
                                    ->getStateUsing(
                                        function ($record) {
                                            return  $record->latestHistory->file ?? null;
                                        }
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
                                TextEntry::make('author.name')
                                    ->label('Created By')
                                    ->badge()
                                    ->icon('heroicon-o-user')
                                    ->color('success'),

                                TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Komentar')
                    ->columnSpanFull()
                    ->schema([
                        CommentsEntry::make('comments')
                            ->disableSidebar()
                            ->perPage(10)
                            ->mentionables(
                                User::whereHas('roles', function ($q) {
                                    $q->whereIn('name', ['staff_dokumen', 'manager_dokumen', 'operation_dokumen']);
                                })->get()
                            )
                    ]),

            ]);
    }
}
