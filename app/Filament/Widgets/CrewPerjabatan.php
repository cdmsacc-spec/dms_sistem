<?php

namespace App\Filament\Widgets;

use App\Models\CrewPkl;
use Illuminate\Support\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CrewPerjabatan extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $periode  = $this->filters['periode']  ?? Carbon::now();
        $carbonDate = $periode instanceof \Carbon\Carbon ? $periode : \Carbon\Carbon::parse($periode);

        $query = CrewPkl::query();

        if ($periode) {
            $query->whereMonth('created_at', $carbonDate->month)
                ->whereYear('created_at', $carbonDate->year);
        }
        $items = $query->where('status_kontrak', 'Active')
            ->whereHas('jabatan', function ($q) {
                $q->whereIn('golongan', ['perwira', 'non-perwira']);
            })
            ->with('jabatan')
            ->get()
            ->filter(fn($item) => $item->jabatan) // pastikan jabatan ada
            ->groupBy(fn($item) => $item->jabatan->golongan)
            ->map(fn($group) => $group->count());

        $counts = $items->toArray();
        $perwira = $counts['perwira'] ?? 0;
        $nonPerwira = $counts['non-perwira'] ?? 0;


        /////////////////////////////////////////////////////////////////////////
        $query2 = CrewPkl::query();


        $satuBulanKeDepan = $carbonDate->copy()->addMonth();
        $jumlahMauExpired = $query2->where('status_kontrak', 'Active')
            ->whereBetween('end_date', [$carbonDate->toDateString(), $satuBulanKeDepan->toDateString()])
            ->count();
        return [
            Stat::make('Crew Perwira', $perwira)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description('jumlah crew pada jabatan perwira'),
            Stat::make('Crew Non Perwira', $nonPerwira)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description('jumlah crew pada jabatan non-perwira'),
            Stat::make('Kontrak Pkl Near Expired', $jumlahMauExpired)
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('info')
                ->textColor('info', 'info', 'gray')
                ->description('kontrak yang akan berakhir dalam satu bulan')

        ];
    }
}
