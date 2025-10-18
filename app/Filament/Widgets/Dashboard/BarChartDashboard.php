<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\Document;
use App\Models\JenisDocument;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BarChartDashboard extends ChartWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;
    protected static ?string $heading = 'Status Perjenis Documment';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 2;


    protected function getData(): array
    {
        $wilayah       = $this->filters['wilayah'] ?? null;
        $perusahaan    = $this->filters['perusahaan'] ?? null;
        $kapal         = $this->filters['kapal'] ?? null;
        $jenis         = $this->filters['jenis'] ?? null;
        $dari_tanggal  = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        // Ambil dokumen dengan filter langsung
        $documents = Document::query()
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
                $wilayah,
                fn($q) =>
                $q->whereHas('kapal.wilayahOperasional', fn($qq) => $qq->where('id', $wilayah))
            )
            ->when(
                $dari_tanggal && $sampai_tanggal,
                fn($q) =>
                $q->whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])
            )
            ->selectRaw('jenis_dokumen_id, status, COUNT(*) as total')
            ->groupBy('jenis_dokumen_id', 'status')
            ->get();

        // Ambil mapping nama dokumen
        $jenisDokumen = JenisDocument::pluck('nama_dokumen', 'id');

        // struktur data
        $expiredData     = [];
        $nearExpiryData  = [];
        $upToDateData    = [];
        $labels          = [];

        foreach ($jenisDokumen as $id => $nama) {
            $labels[] = $nama;

            $grouped = $documents->where('jenis_dokumen_id', $id);
            $total   = $grouped->sum('total');

            if ($total > 0) {
                $expired   = $grouped->where('status', 'Expired')->sum('total');
                $near      = $grouped->where('status', 'Near Expiry')->sum('total');
                $upto      = $grouped->where('status', 'UpToDate')->sum('total');

                $expiredData[]    = round(($expired / $total) * 100, 2);
                $nearExpiryData[] = round(($near / $total) * 100, 2);
                $upToDateData[]   = round(($upto / $total) * 100, 2);
            } else {
                $expiredData[]    = 0;
                $nearExpiryData[] = 0;
                $upToDateData[]   = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Expired',
                    'borderWidth' => 0,
                    'backgroundColor' =>   '#EF4444',
                    'data' => $expiredData,
                ],
                [
                    'label' => 'NearExpiry',
                    'borderWidth' => 0,
                    'backgroundColor' => '#F59E0B',
                    'data' => $nearExpiryData,
                ],
                [
                    'label' => 'UpToDate',
                    'borderWidth' => 0,
                    'backgroundColor' => '#10B981',
                    'data' => $upToDateData,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'max' => 100,

                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
