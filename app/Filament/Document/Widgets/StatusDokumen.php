<?php

namespace App\Filament\Document\Widgets;

use App\Models\Dokumen;
use App\Models\JenisDokumen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatusDokumen extends ChartWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;


    protected ?string $heading = 'Status Dokumen Perjenis';
    protected static ?int $sort = 6;
    protected static bool $isLazy = true;

    public function getColumnSpan(): array|int|string
    {
        return [
            'sm' => 4,
            'md' => 4,
            'lg' => 4,
            'xl' => 2
        ];
    }

    protected function getData(): array
    {
        $wilayah       = $this->filters['wilayah'] ?? null;
        $perusahaan    = $this->filters['perusahaan'] ?? null;
        $kapal         = $this->filters['kapal'] ?? null;
        $jenis         = $this->filters['jenis'] ?? null;
        $dari_tanggal  = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        // Ambil dokumen dengan filter langsung
        $documents = Dokumen::query()
            ->when($jenis, fn($q) => $q->where('id_jenis_dokumen', $jenis))
            ->when(
                $perusahaan,
                fn($q) =>
                $q->whereHas('kapal', fn($qq) => $qq->where('id_perusahaan', $perusahaan))
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
            ->selectRaw('id_jenis_dokumen, status, COUNT(*) as total')
            ->groupBy('id_jenis_dokumen', 'status')
            ->get();

        $jenisDokumen = JenisDokumen::pluck('nama_jenis', 'id');

        $expiredData     = [];
        $nearExpiryData  = [];
        $upToDateData    = [];
        $labels          = [];

        foreach ($jenisDokumen as $id => $nama) {
            $labels[] = $nama;

            $grouped = $documents->where('id_jenis_dokumen', $id);
            $total   = $grouped->sum('total');

            if ($total > 0) {
                $expired   = $grouped->where('status', 'expired')->sum('total');
                $near      = $grouped->where('status', 'near expiry')->sum('total');
                $upto      = $grouped->where('status', 'uptodate')->sum('total');

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
                    'backgroundColor' => '#FACC15',
                    'data' => $nearExpiryData,
                ],
                [
                    'label' => 'UpToDate',
                    'borderWidth' => 0,
                    'backgroundColor' => '#22C55E',
                    'data' => $upToDateData,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'animation' => [
                'duration' => 1000, // durasi animasi (ms)
                'easing' => 'easeOutQuart', // gaya animasi
            ],
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
}
