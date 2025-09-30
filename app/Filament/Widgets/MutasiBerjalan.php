<?php

namespace App\Filament\Widgets;

use App\Models\CrewPkl;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MutasiBerjalan extends ChartWidget
{
    protected static ?string $heading = 'Mutasi Berjalan';
    protected int | string | array $columnSpan = 2;
    protected static bool $isLazy = true;
    protected function getData(): array
    {
        $crewPerMonth = CrewPkl::select(
            DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
            DB::raw("COUNT(*) as total")
        )
            ->whereYear('start_date', now()->year)
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
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
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
