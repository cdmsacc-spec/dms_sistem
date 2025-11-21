<?php

namespace App\Filament\Imports;

use App\Models\JenisKapal;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\WilayahOperasional;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class KapalImporter extends Importer
{
    protected static ?string $model = Kapal::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_kapal')->requiredMapping(),
            ImportColumn::make('status_certified')->requiredMapping(),
            ImportColumn::make('tahun_kapal')->requiredMapping(),
            ImportColumn::make('keterangan')->requiredMapping(),
        ];
    }

    public function resolveRecord(): Kapal
    {
        $data = $this->data;

        $data['id_perusahaan'] = optional(
            Perusahaan::where('nama_perusahaan', 'ILIKE', $data['perusahaan'] ?? null)->first()
        )->id;

        $data['id_jenis_kapal'] = optional(
            JenisKapal::where('nama_jenis', 'ILIKE', $data['jenis_kapal'] ?? null)->first()
        )->id;

        
        $data['id_wilayah'] = optional(
            WilayahOperasional::where('nama_wilayah', 'ILIKE', $data['wilayah'] ?? null)->first()
        )->id;

        unset($data['wilayah']);
        unset($data['perusahaan']);
        unset($data['jenis_kapal']);

        return  Kapal::firstOrNew(["nama_kapal" => $data['nama_kapal']], $data);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your kapal import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
