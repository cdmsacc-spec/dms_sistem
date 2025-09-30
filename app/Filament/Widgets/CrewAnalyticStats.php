<?php

namespace App\Filament\Widgets;

use Filament\Support\Colors\Color;

use App\Models\CrewApplicants;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class CrewAnalyticStats extends BaseWidget
{

    use InteractsWithPageFilters;

    protected ?string $heading = ' Crew Analytics';

    protected function getStats(): array
    {
        $periode  = $this->filters['periode'] ?? null;
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $query = CrewApplicants::query();

        if ($periode) {
            $query->whereMonth('created_at', $carbonDate->month)
                ->whereYear('created_at', $carbonDate->year);
        }
        $counts = $query->select('jenis_kelamin', \DB::raw('COUNT(*) as total'))
            ->whereIn('jenis_kelamin', ['Laki Laki', 'Perempuan'])
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $lakiLaki  = $counts['Laki Laki'] ?? 0;
        $perempuan = $counts['Perempuan'] ?? 0;
        $total     = $lakiLaki + $perempuan;


        $totalStatusActive = CrewApplicants::where('status_proses', 'Active')
            ->whereMonth('created_at', $carbonDate->month)
            ->whereYear('created_at', $carbonDate->year)->count();
        $totalStatusInactive = CrewApplicants::where('status_proses', 'Inactive')
            ->whereMonth('created_at', $carbonDate->month)
            ->whereYear('created_at', $carbonDate->year)->count();


        $data = [
            [
                "label" => "Jumlah Crew",
                "data" => $total,
                "deskripsi" => $lakiLaki . ' crew laki laki dan ' . $perempuan . ' crew perempuan',
                "background_color" => 'primary',
                "query" => null,
            ],
            [
                "label" => "Crew On",
                "data" => $totalStatusActive,
                "deskripsi" => 'Jumlah crew on pada bulan ' . $carbonDate->format('M Y'),
                "background_color" => 'success',
                "query" => 'Active',
            ],
            [
                "label" => "Crew Off",
                "data" => $totalStatusInactive,
                "deskripsi" => 'Jumlah crew off pada bulan ' .  $carbonDate->format('M Y'),
                "background_color" => 'success',
                "query" => 'Inactive',
            ]
        ];

        $stats =  [];

        foreach ($data as $item) {
            $stats[] = Stat::make($item['label'], $item['data'])
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description(
                    $item['deskripsi']
                );
        }
        return $stats;
    }
}
