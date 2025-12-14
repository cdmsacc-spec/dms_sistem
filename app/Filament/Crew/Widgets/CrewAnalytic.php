<?php

namespace App\Filament\Crew\Widgets;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Models\Crew;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CrewAnalytic extends StatsOverviewWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'Crew Analytics';
    protected static bool $isLazy = true;

    public function getColumns(): array|int|null
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 4
        ];
    }
    protected function getStats(): array
    {
        $periode  = Carbon::now();
        $carbonDate = $periode instanceof Carbon ? $periode : Carbon::parse($periode);

        $counts = Crew::query()
            ->whereNot('status', 'rejected')
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->whereIn('jenis_kelamin', ['Laki Laki', 'Perempuan'])
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $lakiLaki  = $counts['Laki Laki'] ?? 0;
        $perempuan = $counts['Perempuan'] ?? 0;
        $total     = $lakiLaki + $perempuan;


        $totalStatusActive = Crew::where('status', 'active')
           
            ->count();

        $totalStatusInactive = Crew::where('status', 'inactive')
          
            ->count();

        $totalStatusStandby = Crew::where('status', 'standby')
          
            ->count();

        $data = [
            [
                "label" => "Crew",
                "data" => $total,
                "deskripsi" => $lakiLaki . ' crew laki laki dan ' . $perempuan . ' crew perempuan',
                "background_color" => 'primary',
                "query" => null,
            ],
            [
                "label" => "Crew Standby",
                "data" => $totalStatusStandby,
                "deskripsi" => 'jumlah crew dengan status standby',
                "background_color" => 'success',
                "query" => 'standby',
            ],
            [
                "label" => "Crew Sign On",
                "data" => $totalStatusActive,
                "deskripsi" => 'jumlah crew dengan status active',
                "background_color" => 'success',
                "query" => 'active',
            ],
            [
                "label" => "Crew Sign Off",
                "data" => $totalStatusInactive,
                "deskripsi" => 'jumlah crew dengan status inactive',
                "background_color" => 'success',
                "query" => 'inactive',
            ]
        ];

        $stats =  [];

        foreach ($data as $item) {
            $stats[] = Stat::make($item['label'], $item['data'])
                ->icon('heroicon-o-user-group')
                ->description(
                    $item['deskripsi']
                )
                  ->chart([10, 10])
                ->extraAttributes(['class' => 'stats-'.$item['label']])
                ->url(auth()->user()?->can('view-any:crew') == true ? AllCrewResource::getUrl() . '?' . http_build_query([
                    'filters' => [
                        'status' => [
                            'value' => $item['query']
                        ],
                    ],
                ]) : false);
        }
        return $stats;
    }
}
