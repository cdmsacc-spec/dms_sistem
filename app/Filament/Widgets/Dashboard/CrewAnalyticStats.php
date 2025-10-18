<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Models\CrewApplicants;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CrewAnalyticStats extends BaseWidget
{
    use InteractsWithPageFilters;

    use HasWidgetShield;

    protected ?string $heading = 'Crew Analytics';
    protected int | string | array $columnSpan = 4;

    protected function getStats(): array
    {
        $counts = CrewApplicants::query()
            ->whereNot('status_proses', StatusCrew::Rejected)
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->whereIn('jenis_kelamin', ['Laki Laki', 'Perempuan'])
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $lakiLaki  = $counts['Laki Laki'] ?? 0;
        $perempuan = $counts['Perempuan'] ?? 0;
        $total     = $lakiLaki + $perempuan;


        $totalStatusActive = CrewApplicants::where('status_proses', 'Active')
            
            ->count();

        $totalStatusInactive = CrewApplicants::where('status_proses', 'Inactive')
            
            ->count();

        $totalStatusStandby = CrewApplicants::where('status_proses', 'Standby')
           
            ->count();

        $data = [
            [
                "label" => "Crew Applicants",
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
                "query" => 'Standby',
            ],
            [
                "label" => "Crew Sign On",
                "data" => $totalStatusActive,
                "deskripsi" => 'jumlah crew dengan status active',
                "background_color" => 'success',
                "query" => 'Active',
            ],
            [
                "label" => "Crew Sign Off",
                "data" => $totalStatusInactive,
                "deskripsi" => 'jumlah crew dengan status inactive',
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
                )
                ->url(auth()->user()?->can('view_any_crewapplicants') == true ? CrewAllResource::getUrl() . '?' . http_build_query([
                    'tableFilters' => [
                        'status_proses' => [
                            'value' => $item['query']
                        ],
                    ],
                ]) : false);
        }
        return $stats;
    }
}
