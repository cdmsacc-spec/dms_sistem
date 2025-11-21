<?php

namespace App\Filament\Crew\Widgets;

use App\Filament\Crew\Resources\AllCrews\AllCrewResource;
use App\Models\Crew;
use App\Models\CrewKontrak;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrewJabatanGroup extends StatsOverviewWidget
{
    use HasWidgetShield, InteractsWithPageFilters;
    protected ?string $heading = 'Crew Perjabatan';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof Carbon ? $periode : Carbon::parse($periode);

        $query = CrewKontrak::query();
        $items = $query->where('status_kontrak', 'active')
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

        $query2 = Crew::query();
        $crewRjected = $query2->where('status', 'rejected')->count();
        return [
            Stat::make('Crew Perwira', $totalPerwira)
                ->icon('heroicon-o-user-group')
                ->description("{$perwiraDeck} divisi deck, {$perwiraMesin} divisi mesin")
                ->url(auth()->user()?->can('view-any:crew') == true ? AllCrewResource::getUrl() . '?' . http_build_query([
                    'filters' => [
                        'jabatan' => [
                            'value' => 'perwira'
                        ],
                    ],
                ]) : false),
            Stat::make('Crew Non Perwira', $totalNonPerwira)
                ->icon('heroicon-o-user-group')
                ->description("{$nonPerwiraDeck} divisi deck, {$nonPerwiraMesin} divisi mesin")
                ->url(auth()->user()?->can('view-any:crew') == true ? AllCrewResource::getUrl() . '?' . http_build_query([
                    'filters' => [
                        'jabatan' => [
                            'value' => 'non-perwira'
                        ],
                    ],
                ]) : false),
            Stat::make('Crew Rejected', $crewRjected)
                ->icon('heroicon-o-user-group')
                ->description('jumlah crew yang gagal dalam interview')
                ->url(auth()->user()?->can('view-any:crew') == true ? AllCrewResource::getUrl() . '?' . http_build_query([
                    'filters' => [
                        'status' => [
                            'value' => 'rejected'
                        ],
                    ],
                ]) : false)

        ];
    }
}
