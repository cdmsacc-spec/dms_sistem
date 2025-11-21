<?php

namespace App\Filament\Imports;

use App\Models\JenisKapal;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class JenisKapalImporter extends Importer
{
    protected static ?string $model = JenisKapal::class;
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_jenis')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('deskripsi')->requiredMapping()->rules(['required', 'max:255'])->guess(['description', 'deskripsi', 'Deskripsi']),
        ];
    }

    public function resolveRecord(): JenisKapal
    {
        return JenisKapal::firstOrNew([
            'nama_jenis' => $this->data['nama_jenis'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your jenis kapal import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
