<?php

namespace App\Filament\Document\Widgets;

use App\Models\Dokumen;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StatusAllDokumen extends ChartWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;
    protected ?string $heading = 'Status All Dokumen';
    protected static ?int $sort = 5;
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
        $perusahaan = $this->filters['perusahaan'] ?? null;
        $kapal = $this->filters['kapal'] ?? null;
        $jenis = $this->filters['jenis'] ?? null;
        $dari_tanggal = $this->filters['dari_tanggal'] ?? null;
        $sampai_tanggal = $this->filters['sampai_tanggal'] ?? null;

        $results = Dokumen::query()
            ->when($jenis, fn($q) => $q->where('id_jenis_dokumen', $jenis))
            ->when($perusahaan, fn($q) => $q->whereHas('kapal', fn($qq) => $qq->where('id_perusahaan', $perusahaan)))
            ->when($kapal, fn($q) => $q->whereHas('kapal', fn($qq) => $qq->where('id', $kapal)))
            ->when($dari_tanggal && $sampai_tanggal, fn($q) => $q->whereBetween('created_at', [$dari_tanggal, $sampai_tanggal]))
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['uptodate', 'expired', 'near expiry'])
            ->groupBy('status')
            ->pluck('total', 'status');
        $uptodate = $results['uptodate'] ?? 0;
        $expired = $results['expired'] ?? 0;
        $near_expiry = $results['near expiry'] ?? 0;
        return [
            'datasets' => [

                [
                    'label' => 'Expired',
                    'borderWidth' => 0,
                    'data' => [$expired],
                    'backgroundColor' => ['#EF4444'],
                ],
                [
                    'label' => 'Near Expiry',
                    'borderWidth' => 0,
                    'data' => [$near_expiry],
                    'backgroundColor' =>  '#FACC15',
                ],
                [
                    'label' => 'UpToDate',
                    'borderWidth' => 0,
                    'data' => [$uptodate],
                    'backgroundColor' => '#22C55E',
                ]

            ],
            'labels' => [''],
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutQuart',
            ],
        ];
    }
}
