<?php

namespace App\Filament\Crew\Widgets;

use App\Models\CrewKontrak;
use App\Models\CrewSignOff;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class CrewActivityBerjalan extends ChartWidget
{
    protected ?string $heading = 'Crew Activity Berjalan';
    
    use HasWidgetShield, InteractsWithPageFilters;

    public function getColumnSpan(): array|int|string
    {
        return [
            'sm' => 4,
            'md' => 4,
            'lg' => 4,
            'xl' => 2
        ];
    }

    protected static bool $isLazy = true;
    protected function getData(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $crewMutasiPerMonth = CrewKontrak::select(
            DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
            DB::raw("COUNT(*) as total")
        )
            ->whereYear('start_date', $carbonDate->year)
            ->where('kategory', 'promosi')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $crewSignonPerMonth = CrewKontrak::select(
            DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
            DB::raw("COUNT(*) as total")
        )
            ->whereYear('start_date', $carbonDate->year)
            ->where('kategory', 'signon')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $crewSignoffPerMonth = CrewSignOff::select(
            DB::raw("EXTRACT(MONTH FROM tanggal) as bulan"),
            DB::raw("COUNT(*) as total")
        )
            ->whereYear('tanggal', $carbonDate->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $seriesMutasi = [];
        foreach (range(1, 12) as $bulan) {
            $seriesMutasi[] = $crewMutasiPerMonth[$bulan] ?? 0;
        }

        $seriesDataSignon = [];
        foreach (range(1, 12) as $bulan) {
            $seriesDataSignon[] = $crewSignonPerMonth[$bulan] ?? 0;
        }

          $seriesDataSignoff = [];
        foreach (range(1, 12) as $bulan) {
            $seriesDataSignoff[] = $crewSignoffPerMonth[$bulan] ?? 0;
        }


        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Mutasi/Promosi',
                    'data' => $seriesMutasi,
                    'backgroundColor' => '#3bc4ff',
                    'borderWidth' => 0,
                ],
                [
                    'label' => 'Sign on',
                    'data' => $seriesDataSignon,
                    'backgroundColor' => '#1CED23',
                    'borderWidth' => 0,
                ],
                 [
                    'label' => 'Sign off',
                    'data' => $seriesDataSignoff,
                    'backgroundColor' => '#fc0303',
                    'borderWidth' => 0,
                ],
            ],
        ];
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
                'y' => [
                    'ticks' => [
                        'stepSize' => 1, // pastikan hanya angka bulat 
                    ],
                ],

            ],
        ];
    }
    protected function getType(): string
    {
        return 'bar';
    }
}
