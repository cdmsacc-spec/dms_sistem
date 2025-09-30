<?php

namespace App\Filament\StaffCrew\Resources\CrewOverviewResource\Widget;

use App\Models\CrewApplicants;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class CrewOverviewStats extends BaseWidget
{

        protected static bool $isLazy = true;

    protected function getStats(): array
    {

        $counts = CrewApplicants::query()
            ->select('status_proses', \DB::raw('COUNT(*) as total'))
            ->whereIn('status_proses', ['Draft', 'Ready For Interview',  'Inactive',  'Standby',  'Active'])
            ->groupBy('status_proses')
            ->pluck('total', 'status_proses');


        $active   = $counts['Active'] ?? 0;
        $inactive = $counts['Inactive'] ?? 0;
        $draft    = $counts['Draft'] ?? 0;
        $standby = $counts['Standby'] ?? 0;
        $readyForInterview = $counts['Ready For Interview'] ?? 0;

        $data = [
         
            [
                "total_data" => $readyForInterview,
                "color" => 'warning',
                "label" => 'Crew Ready For Interview',
            ],
              [
                "total_data" => $standby,
                "color" => 'info',
                "label" => 'Crew Stanby',
            ],
            [
                "total_data" => $active,
                "color" => 'success',
                "label" => 'Crew Active',
            ],
            [
                "total_data" => $inactive,
                "color" => 'danger',
                "label" => 'Crew Inactive',
            ]
        ];

        $stats =  [];
        foreach ($data as $value) {
            $stats[] = Stat::make('A', $value['total_data'])
                ->icon('heroicon-o-folder-open')
                ->label(new HtmlString('<span class="text-xs font-medium text-' . $value['color'] . '-600">' . $value['label'] . '</span>'))
                ->description('')
                ->chart([10, 10])
                ->color($value['color']);
        }

        return $stats;
    }
}
