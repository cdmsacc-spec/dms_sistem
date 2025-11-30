<?php

namespace App\Filament\Imports;

use App\Models\Perusahaan;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class PerusahaanImporter extends Importer
{
    protected static ?string $model = Perusahaan::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_perusahaan')->requiredMapping(),
            ImportColumn::make('kode_perusahaan')->requiredMapping(),
            ImportColumn::make('alamat')->requiredMapping(),
            ImportColumn::make('email')->requiredMapping(),
            ImportColumn::make('telp')->requiredMapping(),
            ImportColumn::make('npwp')->requiredMapping(),
            ImportColumn::make('keterangan')->requiredMapping(),

        ];
    }

    public function resolveRecord(): Perusahaan
    {
        return Perusahaan::firstOrNew([
            'nama_perusahaan' => $this->data['nama_perusahaan'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your perusahaan import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
