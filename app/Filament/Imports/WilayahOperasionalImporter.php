<?php

namespace App\Filament\Imports;

use App\Models\WilayahOperasional;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class WilayahOperasionalImporter extends Importer
{
    protected static ?string $model = WilayahOperasional::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_wilayah')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('kode_wilayah')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('deskripsi')->requiredMapping()->rules(['required', 'max:255'])->guess(['description', 'deskripsi', 'Deskripsi']),
            ImportColumn::make('ttd_dibuat')->requiredMapping(),
            ImportColumn::make('ttd_diperiksa')->requiredMapping(),
            ImportColumn::make('ttd_diketahui_1')->requiredMapping(),
            ImportColumn::make('ttd_diketahui_2')->requiredMapping(),
            ImportColumn::make('ttd_disetujui_1')->requiredMapping(),
            ImportColumn::make('ttd_disetujui_2')->requiredMapping(),
        ];
    }

    public function resolveRecord(): WilayahOperasional
    {
        return WilayahOperasional::firstOrNew([
            'nama_wilayah' => $this->data['nama_wilayah'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your wilayah operasional import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
