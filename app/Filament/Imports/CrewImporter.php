<?php

namespace App\Filament\Imports;

use App\Models\Crew;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class CrewImporter extends Importer
{
    protected static ?string $model = Crew::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_crew')->requiredMapping(),
            ImportColumn::make('posisi_dilamar')->requiredMapping(),
            ImportColumn::make('tempat_lahir')->requiredMapping(),
            ImportColumn::make('tanggal_lahir')->requiredMapping(),
            ImportColumn::make('jenis_kelamin')->requiredMapping(),
            ImportColumn::make('golongan_darah')->requiredMapping(),
            ImportColumn::make('status_identitas')->requiredMapping(),
            ImportColumn::make('agama')->requiredMapping(),
            ImportColumn::make('no_hp')->requiredMapping(),
            ImportColumn::make('no_hp_rumah')->requiredMapping(),
            ImportColumn::make('email')->requiredMapping(),
            ImportColumn::make('kebangsaan')->requiredMapping(),
            ImportColumn::make('suku')->requiredMapping(),
            ImportColumn::make('alamat_ktp')->requiredMapping(),
            ImportColumn::make('alamat_sekarang')->requiredMapping(),
            ImportColumn::make('status_rumah')->requiredMapping(),
            ImportColumn::make('tinggi_badan')->requiredMapping(),
            ImportColumn::make('berat_badan')->requiredMapping(),
            ImportColumn::make('ukuran_waerpack')->requiredMapping(),
            ImportColumn::make('ukuran_sepatu')->requiredMapping(),


        ];
    }
    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('status')
                ->label('Status')
                ->placeholder('')
                ->native(false)
                ->options([
                    'draft' => 'draft',
                    'ready for interview' => 'ready for interview',
                ])
                ->required(),
        ];
    }

    public function resolveRecord(): Crew
    {
        $data = $this->data;
        $status = $this->options['status'];
        $crewKey = [
            'nama_crew',
            'posisi_dilamar',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'golongan_darah',
            'status_identitas',
            'agama',
            'no_hp',
            'no_hp_rumah',
            'email',
            'kebangsaan',
            'suku',
            'alamat_ktp',
            'alamat_sekarang',
            'status_rumah',
            'tinggi_badan',
            'berat_badan',
            'ukuran_waerpack',
            'ukuran_sepatu',
        ];
        $crewData = [];

        foreach ($crewKey as $key) {
            if (isset($data[$key])) {
                $crewData[$key] = $data[$key];
                $crewData['status'] = $status;
                unset($data[$key]);
            }
        }

        $nokData = [
            'nama' => $data['nok_nama'],
            'hubungan' => $data['nok_hubungan'],
            'alamat' => $data['nok_alamat'],
            'no_hp' => $data['nok_hp'],
        ];

        $crews =  Crew::firstOrCreate(['email' => $crewData['email'], 'no_hp' => $crewData['no_hp']], $crewData);
        $crews->nok()->create($nokData);

        return $crews;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your crews import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
