<?php

namespace App\Filament\Crew\Resources\AllCrews\Pages\Detail;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Models\CrewKontrak;
use App\Models\ReminderCrew;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class DetailKontrak extends Page
{
    protected static string $resource = AllCrewResource::class;
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.crew.resources.detail-kontrak';
    public ?int $record = null;
    public $crewPkl;
    public function mount(): void
    {
        if ($this->record) {
            $this->crewPkl =  CrewKontrak::findOrFail($this->record);
        }
    }
   
    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->record($this->crewPkl)
            ->schema([

                // ===========================
                // Section 1: Dokumen
                // ===========================
                Section::make('Dokumen')
                    ->description('Informasi dokumen crew, status kontrak, file dokumen, dan reminder')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('nomor_dokumen')
                            ->label('Nomor Dokumen'),
                        TextEntry::make('kategory')
                            ->label('Kategori Dokumen'),
                        TextEntry::make('status_kontrak')
                            ->label('Status Kontrak')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'active' => 'success',
                                'expired' => 'danger',
                                'waiting approval' => 'warning',
                                default => 'secondary',
                            }),
                        TextEntry::make('file')
                            ->label('File Dokumen')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($state) => $state != null ? 'Download' : '')
                            ->url(fn($record) => $record->file ? asset('storage/' . $record->file) : null, shouldOpenInNewTab: true),
                        TextEntry::make('nomor_dokumen')
                            ->label('Reminder')
                            ->badge()
                            ->color('warning')
                            ->placeholder('Tanpa Reminder')
                            ->formatStateUsing(function ($record) {
                                if (!$record) return null;
                                if ($record->status_kontrak !== 'active') return null;
                                $reminders = ReminderCrew::all();
                                if ($reminders->isEmpty()) return null;
                                if (!$record->end_date) return null;

                                $endDate = Carbon::parse($record->end_date);
                                $datesWithTime = $reminders->map(function ($reminder) use ($endDate) {
                                    $daysArray = is_array($reminder->reminder_hari)
                                        ? $reminder->reminder_hari
                                        : explode(',', $reminder->reminder_hari);

                                    return collect($daysArray)->map(function ($days) use ($endDate, $reminder) {
                                        $dateTime = $endDate->copy()->subDays((int)$days);
                                        if ($reminder->reminder_jam) {
                                            [$hour, $minute] = explode(':', $reminder->reminder_jam);
                                            $dateTime->setTime((int)$hour, (int)$minute);
                                        } else {
                                            $dateTime->startOfDay();
                                        }
                                        return $dateTime->format('d-m-Y H:i');
                                    });
                                })->flatten();

                                return $datesWithTime->implode(' . ');
                            })
                            ->listWithLineBreaks(),
                    ]),

                // ===========================
                // Section 2: Info Crew & Perusahaan
                // ===========================
                Section::make('Info Crew')
                    ->description('Data crew beserta perusahaan, kapal, wilayah, dan jabatan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('crew.nama_crew')->label('Nama Crew'),
                        TextEntry::make('perusahaan.nama_perusahaan')->label('Perusahaan'),
                        TextEntry::make('kapal.nama_kapal')->label('Kapal'),
                        TextEntry::make('wilayah.nama_wilayah')->label('Wilayah'),
                        TextEntry::make('jabatan.nama_jabatan')->label('Jabatan'),
                    ]),

                // ===========================
                // Section 3: Kontrak & Gaji
                // ===========================
                Section::make('Kontrak & Gaji')
                    ->description('Informasi kontrak, gaji, jenis kontrak, tanggal mulai & selesai, dan summary appraisal')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('gaji')
                            ->label('Gaji')
                            ->money('IDR'),
                        TextEntry::make('kontrak_lanjutan')
                            ->label('Jenis Kontrak')
                            ->formatStateUsing(fn($state) => $state == 1 ? 'Lanjutan' : 'Baru'),
                        TextEntry::make('start_date')->label('Mulai Kontrak'),
                        TextEntry::make('end_date')->label('Selesai Kontrak'),
                        TextEntry::make('summary')
                            ->label('Summary Penilaian')
                            ->getStateUsing(function ($record) {
                                $appraisals = $record->appraisal->pluck('nilai');
                                if ($appraisals->isEmpty()) {
                                    return 'Belum Ada Penilaian';
                                }
                                $average = round($appraisals->avg());
                                return match (true) {
                                    $average >= 100 => "Sangat Memuaskan ($average)",
                                    $average >= 75  => "Memuaskan ($average)",
                                    $average >= 50  => "Cukup Memuaskan ($average)",
                                    $average >= 25  => "Tidak Memuaskan ($average)",
                                    default => "Belum Dinilai",
                                };
                            }),
                    ]),
            ]);
    }
}
