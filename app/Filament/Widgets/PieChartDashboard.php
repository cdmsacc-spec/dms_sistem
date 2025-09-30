<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\Colors\Color;

class PieChartDashboard extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'Status All Documment';
    protected static ?int $sort = 2;
     public  function getMaxHeight(): ?string
    {
        return '38vh';
    }

    protected function getData(): array
    {
        $perusahaan    = $this->filters['perusahaan'] ?? null;
        $kapal         = $this->filters['kapal'] ?? null;
        $jenis         = $this->filters['jenis'] ?? null;
        $dari_tanggal  = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        // Query sekali 
        $results = Document::query()
            ->when($jenis, fn($q) => $q->where('jenis_dokumen_id', $jenis))
            ->when(
                $perusahaan,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('perusahaan_id', $perusahaan))
            )
            ->when(
                $kapal,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('id', $kapal))
            )
            ->when(
                $dari_tanggal && $sampai_tanggal,
                fn($q) =>
                $q->whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])
            )
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['UpToDate', 'Expired', 'Near Expiry'])
            ->groupBy('status')
            ->pluck('total', 'status');

        // Ambil hasil (default 0 kalau tidak ada)
        $uptodate    = $results['UpToDate']    ?? 0;
        $expired     = $results['Expired']     ?? 0;
        $near_expiry = $results['Near Expiry'] ?? 0;


        return [
            'datasets' => [
                [
                    'backgroundColor' => [
                        'rgba(254, 0, 4, 0.5)',   // merah transparan
                        'rgba(244, 197, 11, 0.5)', // kuning transparan
                        'rgba(16, 224, 0, 0.5)',  // hijau transparan
                    ],
                    'borderColor' => [
                        'rgba(254, 0, 4, 1)',   // merah solid
                        'rgba(244, 197, 11, 1)', // kuning solid
                        'rgba(16, 224, 0, 1)',  // hijau solid
                    ],
                    'data' => [$expired, $near_expiry, $uptodate],
                ],
            ],
            'labels' => ['Expired', 'NearExpiry', 'UpToDate'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
