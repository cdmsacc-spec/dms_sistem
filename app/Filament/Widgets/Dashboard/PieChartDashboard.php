<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\Document;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\Colors\Color;

class PieChartDashboard extends ChartWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected static ?string $heading = 'Status All Documment';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 2;

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
                        '#EF4444',   // merah transparan
                        '#F59E0B', // kuning transparan
                        '#10B981',  // hijau transparan
                    ],
                    'borderColor' => [
                        '#EF4444',   // merah transparan
                        '#F59E0B', // kuning transparan
                        '#10B981',  // hijau transparan
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
