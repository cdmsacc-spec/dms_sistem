<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\CrewApplicants;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class PenggolonganUsia extends ChartWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected static ?string $heading = 'Penggolongan Usia';
    protected int | string | array $columnSpan = 2;
    protected static bool $isLazy = true;
    protected function getData(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $usiaGroups = CrewApplicants::selectRaw("
            CASE 
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) < 20 THEN '<20'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 20 AND 29 THEN '20-29'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 30 AND 39 THEN '30-39'
                WHEN EXTRACT(YEAR FROM AGE(NOW(), tanggal_lahir)) BETWEEN 40 AND 49 THEN '40-49'
                ELSE '50+'
            END as kategori_usia,
            COUNT(*) as total
        ")
           
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
