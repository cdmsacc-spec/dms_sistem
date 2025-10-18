<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\StatusCrew;
use App\Filament\Crew\Resources\CrewAllResource;
use App\Models\CrewApplicants;
use App\Models\CrewPkl;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CrewPerjabatan extends BaseWidget
{
    use HasWidgetShield;

    use InteractsWithPageFilters;


    protected function getStats(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $query = CrewPkl::query();

        //  $items = $query->where('status_kontrak', 'Active')
        //      ->whereHas('jabatan', function ($q) {
        //          $q->whereIn('golongan', ['perwira', 'non-perwira']);
        //      })
        //      ->with('jabatan')
        //      ->get()
        //      ->filter(fn($item) => $item->jabatan) // pastikan jabatan ada
        //      ->groupBy(fn($item) => $item->jabatan->golongan)
        //      ->map(fn($group) => $group->count());

        $items = $query->where('status_kontrak', 'Active')
            ->whereHas('jabatan', function ($q) {
                $q->whereIn('golongan', ['perwira', 'non-perwira']);
            })
            ->when($carbonDate, function ($query, $carbonDate) {
                return $query->whereMonth('created_at', $carbonDate->month)
                    ->whereYear('created_at', $carbonDate->year);
            })
            ->with('jabatan')
            ->get()
            ->filter(fn($item) => $item->jabatan) // pastikan ada relasi jabatan
            ->groupBy(fn($item) => $item->jabatan->golongan . '-' . $item->jabatan->devisi)
            ->map(fn($group) => $group->count());

        $counts = $items->toArray();
        // Ambil masing-masing kombinasi
        $perwiraDeck = $counts['perwira-Deck'] ?? 0;
        $perwiraMesin = $counts['perwira-Mesin'] ?? 0;
        $totalPerwira     = $perwiraDeck + $perwiraMesin;

        $nonPerwiraDeck = $counts['non-perwira-Deck'] ?? 0;
        $nonPerwiraMesin = $counts['non-perwira-Mesin'] ?? 0;
        $totalNonPerwira     = $nonPerwiraDeck + $nonPerwiraMesin;

        //$counts = $items->toArray();
        //$perwira = $counts['perwira'] ?? 0;
        //$nonPerwira = $counts['non-perwira'] ?? 0;


        /////////////////////////////////////////////////////////////////////////
        $query2 = CrewApplicants::query();
        $crewRjected = $query2->where('status_proses', StatusCrew::Rejected->value)->count();
        return [
            Stat::make('Crew Perwira', $totalPerwira)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description("{$perwiraDeck} devisi deck, {$perwiraMesin} devisi mesin")
                ->url(auth()->user()?->can('view_any_crewapplicants') == true ? CrewAllResource::getUrl() . '?' . http_build_query([
                    'tableFilters' => [
                        'jabatan' => [
                            'value' => 'perwira'
                        ],
                    ],
                ]) : false),
            Stat::make('Crew Non Perwira', $totalNonPerwira)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description("{$nonPerwiraDeck} devisi deck, {$nonPerwiraMesin} devisi mesin")
                ->url(auth()->user()?->can('view_any_crewapplicants') == true ? CrewAllResource::getUrl() . '?' . http_build_query([
                    'tableFilters' => [
                        'jabatan' => [
                            'value' => 'non-perwira'
                        ],
                    ],
                ]) : false),
            Stat::make('Crew Rejected', $crewRjected)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description('jumlah crew yang gagal dalam interview')
                ->url(auth()->user()?->can('view_any_crewapplicants') == true ? CrewAllResource::getUrl() . '?' . http_build_query([
                    'tableFilters' => [
                        'status_proses' => [
                            'value' => StatusCrew::Rejected->value
                        ],
                    ],
                ]) : false)

        ];
    }
}
