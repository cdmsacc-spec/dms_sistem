<?php

namespace App\Filament\Crew\Widgets;

use App\Models\CrewPkl;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MutasiBerjalan extends ChartWidget
{

    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected static ?string $heading = 'Mutasi Berjalan';
    protected int | string | array $columnSpan = 2;
    protected static bool $isLazy = true;
    protected function getData(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $crewPerMonth = CrewPkl::select(
            DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
            DB::raw("COUNT(*) as total")
        )
            ->whereYear('start_date', $carbonDate->year)
            ->where('kategory', 'Promosi')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $seriesData = [];
        foreach (range(1, 12) as $bulan) {
            $seriesData[] = $crewPerMonth[$bulan] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Mutasi',
                    'data' => $seriesData,
                    'backgroundColor' => '#3bc4ff',
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
