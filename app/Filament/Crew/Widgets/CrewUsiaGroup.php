<?php

namespace App\Filament\Crew\Widgets;

use App\Models\Crew;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class CrewUsiaGroup extends ChartWidget
{
    protected ?string $heading = 'Crew Usia Group';
    protected static bool $isLazy = true;

    use HasWidgetShield,InteractsWithPageFilters;
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
        $periode  = $this->filters['periode']  ?? null;

        $usiaGroups = Crew::selectRaw("
            CASE 
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) < 20 THEN '<20'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 20 AND 29 THEN '20-29'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 30 AND 39 THEN '30-39'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 40 AND 49 THEN '40-49'
                ELSE '50+'
            END as kategori_usia,
            COUNT(*) as total
        ")
            ->when($periode, function ($query, $periode) {
                $carbonDate = $periode instanceof \Carbon\Carbon
                    ? $periode
                    : \Carbon\Carbon::parse($periode);

                return $query
                    ->whereMonth('created_at', $carbonDate->month)
                    ->whereYear('created_at', $carbonDate->year);
            })
            ->groupBy('kategori_usia')
            ->pluck('total', 'kategori_usia')
            ->toArray();

        $order = ['<20', '20-29', '30-39', '40-49', '50+'];


        $datasets = [];
        $colors = ['#3bc4ff', '#1CED23', '#f59e0b', '#ED8134', '#ED1C1C'];

        foreach ($order as $index => $kategori) {
            $jumlah = $usiaGroups[$kategori] ?? 0;

            $datasets[] = [
                'label' => "Usia $kategori",
                'data' => [$jumlah], // satu data per kategori
                'backgroundColor' => $colors[$index],
                'borderWidth' => 0,
            ];
        }

        return [
            'labels' => ['Crew'], // sumbu X tunggal, karena tiap dataset punya label sendiri
            'datasets' => $datasets,
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
